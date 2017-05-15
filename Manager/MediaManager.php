<?php

namespace Tms\Bundle\MediaBundle\Manager;

use Doctrine\ORM\EntityManager;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tms\Bundle\MediaBundle\Event\MediaEvent;
use Tms\Bundle\MediaBundle\Event\MediaEvents;
use Tms\Bundle\MediaBundle\StorageMapper\StorageMapperInterface;
use Tms\Bundle\MediaBundle\MetadataExtractor\MetadataExtractorInterface;
use Tms\Bundle\MediaBundle\Media\Transformer\MediaTransformerInterface;
use Tms\Bundle\MediaBundle\Entity\Media;
use Tms\Bundle\MediaBundle\Exception\NoMatchedTransformerException;
use Tms\Bundle\MediaBundle\Exception\MediaNotFoundException;
use Tms\Bundle\MediaBundle\Exception\MediaAlreadyExistException;

/**
 * Media manager.
 *
 * @author Gabriel Bondaz <gabriel.bondaz@idci-consulting.fr>
 */
class MediaManager extends AbstractManager
{
    protected $configuration;
    protected $filesystemMap;
    protected $metadataExtractors;
    protected $mediaTransformers;

    /**
     * Guess reference prefix
     *
     * @param array|Media $media
     *
     * @return string|null.
     */
    public static function guessReferencePrefix($media)
    {
        $metadata = array();
        $source   = null;

        if ($media instanceof Media) {
            $metadata = $media->getMetadata();
            $source   = $media->getSource();
        } else {
            $metadata = $media['metadata'];
            $source   = $media['source'];
        }

        $nodes = array();
        if (isset($metadata['customer'])) {
            $nodes[] = $metadata['customer'];
        }
        if (isset($metadata['offer'])) {
            $nodes[] = $metadata['offer'];
        }
        if (!empty($nodes)) {
            return implode('/', $nodes);
        }

        if (!empty($source)) {
            return $source;
        }

        return null;
    }

    /**
     * Setup parameters.
     *
     * @param OptionsResolverInterface $resolver.
     * @return array
     */
    protected function setupParameters(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'api_public_endpoint',
                'cache_directory',
                'media',
                'storage_provider',
                'working_directory',
            ))
            ->setDefaults(array(
                'description'        => null,
                'extension'          => null,
                'ip_source'          => null,
                'metadata'           => array(),
                'mime_type'          => null,
                'name'               => null,
                'processing_file'    => null,
                'size'               => null,
                'source'             => null,
                'reference'          => null,
                'reference_prefix'   => null,
            ))
            ->setAllowedTypes(array(
                'api_public_endpoint' => array('string'),
                'cache_directory'     => array('string'),
                'description'         => array('null', 'string'),
                'extension'           => array('null', 'string'),
                'ip_source'           => array('null', 'string'),
                'media'               => array('Symfony\Component\HttpFoundation\File\UploadedFile'),
                'metadata'            => array('null', 'string', 'array'),
                'mime_type'           => array('null', 'string'),
                'name'                => array('null', 'string'),
                'processing_file'     => array('null', 'Symfony\Component\HttpFoundation\File\File'),
                'size'                => array('null', 'integer'),
                'source'              => array('null', 'string'),
                'storage_provider'    => array('string'),
                'working_directory'   => array('string'),
                'reference'           => array('null', 'string'),
                'reference_prefix'    => array('null', 'string'),
            ))
            ->setNormalizers(array(
                'description'      => function(Options $options, $value) {
                    if (null !== $value) {
                        return $value;
                    }

                    return $options['media']->getClientOriginalName();
                },
                'extension'        => function(Options $options, $value) {
                    return $options['media']->guessExtension();
                },
                'metadata'         => function(Options $options, $value) {
                    if (null === $value) {
                        return array();
                    }

                    if (is_array($value)) {
                        return $value;
                    }

                    $decodedMetadata = json_decode($value, true);

                    if (null === $decodedMetadata) {
                        return array();
                    }

                    return $decodedMetadata;
                },
                'mime_type'        => function(Options $options, $value) {
                    return $options['media']->getMimeType();
                },
                'name'             => function(Options $options, $value) {
                    if (null !== $value) {
                        return $value;
                    }

                    return $options['media']->getClientOriginalName();
                },
                'processing_file'  => function(Options $options, $value) {
                    return $options['media']->move(
                        $options['working_directory'],
                        uniqid('tmp_media_')
                    );
                },
                'size'             => function(Options $options, $value) {
                    return $options['processing_file']->getSize();
                },
                'reference'        => function(Options $options, $value) {
                    $now = new \DateTime();

                    return sprintf('%s-%s-%s-%d',
                        sprintf("%u", crc32($options['source'])),
                        $now->format('U'),
                        md5(sprintf("%s%s%s",
                            $options['mime_type'],
                            $options['name'],
                            $options['size']
                        )),
                        rand(0, 9999)
                    );
                },
                'reference_prefix' => function(Options $options, $value) {
                    return MediaManager::guessReferencePrefix($options);
                },
            ))
        ;
    }

    /**
     * Constructor
     *
     * @param array                    $configuration
     * @param EntityManager            $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param FilesystemMap            $filesystemMap
     */
    public function __construct(
        array $configuration = array(),
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        FilesystemMap $filesystemMap
    )
    {
        parent::__construct($entityManager, $eventDispatcher);

        $this->configuration      = $configuration;
        $this->filesystemMap      = $filesystemMap;
        $this->metadataExtractors = array();
        $this->mediaTransformers  = array();
    }

    /**
     * Returns the filesystem map.
     *
     * @return FilesystemMap
     */
    public function getFilesystemMap()
    {
        return $this->filesystemMap;
    }

    /**
     * Return the configuration
     *
     * @param string $key The configuration key to retrieve if given.
     * @return mixed
     */
    public function getConfiguration($key = null)
    {
        if (null === $key) {
            return $this->configuration;
        }

        if (isset($this->configuration[$key])) {
            return $this->configuration[$key];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return "TmsMediaBundle:Media";
    }

    /**
     * {@inheritdoc}
     */
    public function add($entity, $flush = true)
    {
        $this->getEventDispatcher()->dispatch(
            MediaEvents::PRE_CREATE,
            new MediaEvent($entity)
        );

        parent::add($entity, $flush);

        $this->getEventDispatcher()->dispatch(
            MediaEvents::POST_CREATE,
            new MediaEvent($entity)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update($entity)
    {
        $this->getEventDispatcher()->dispatch(
            MediaEvents::PRE_UPDATE,
            new MediaEvent($entity)
        );

        parent::update($entity);

        $this->getEventDispatcher()->dispatch(
            MediaEvents::POST_UPDATE,
            new MediaEvent($entity)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity)
    {
        $this->getEventDispatcher()->dispatch(
            MediaEvents::PRE_DELETE,
            new MediaEvent($entity)
        );

        parent::delete($entity);

        $this->getEventDispatcher()->dispatch(
            MediaEvents::POST_DELETE,
            new MediaEvent($entity)
        );
    }

    /**
     * Add metadata extractor
     *
     * @param MetadataExtractorInterface $metadataExtractor
     * @return self
     */
    public function addMetadataExtractor(MetadataExtractorInterface $metadataExtractor)
    {
        $this->metadataExtractors[] = $metadataExtractor;

        return $this;
    }

    /**
     * Guess a metadata extractor based on the given mime type
     *
     * @param string $mimeType
     * @return MetadataExtractorInterface
     */
    protected function guessMetadataExtractor($mimeType)
    {
        foreach ($this->metadataExtractors as $metadataExtractor) {
            if ($metadataExtractor->checkMimeType($mimeType)) {
                return $metadataExtractor;
            }
        }
    }

    /**
     * Add media transformer
     *
     * @param MediaTransformerInterface $mediaTransformer
     * @return self
     */
    public function addMediaTransformer(MediaTransformerInterface $mediaTransformer)
    {
        $this->mediaTransformers[] = $mediaTransformer;

        return $this;
    }

    /**
     * Guess a transformer on the given format
     *
     * @param string $format
     * @return MediaTransformerInterface
     */
    protected function guessMediaTransformer($format)
    {
        foreach ($this->mediaTransformers as $mediaTransformer) {
            if ($mediaTransformer->checkFormat($format)) {
                return $mediaTransformer;
            }
        }

        throw new NoMatchedTransformerException($format);
    }

    /**
     * Retrieve mediaRaw
     *
     * @param string $reference
     * @return array The media
     */
    public function retrieveMedia($reference)
    {
        $media = $this->findOneBy(array('reference' => $reference));

        if (!$media) {
            throw new MediaNotFoundException($reference);
        }

        return $media;
    }

    /**
     * Build the storage key
     *
     * @param string $referencePrefix
     * @param string $reference
     * @return string
     */
    public function buildStorageKey($referencePrefix, $reference)
    {
        if (null === $referencePrefix) {
            return $reference;
        }

        return sprintf('%s/%s', $referencePrefix, $reference);
    }

    /**
     * Add Media
     *
     * @param array $parameters
     * @return Media
     */
    public function addMedia(array $parameters)
    {
        $resolver = new OptionsResolver();
        $this->setupParameters($resolver);
        $resolvedParameters = $resolver->resolve(array_merge(
            $this->getConfiguration(),
            $parameters
        ));

        $media = $this->findOneBy(array(
            'reference' => $resolvedParameters['reference']
        ));

        if (null !== $media) {
            throw new MediaAlreadyExistException();
        }

        $provider = $this->getFilesystemMap()->get($resolvedParameters['storage_provider']);

        $provider->write(
            $this->buildStorageKey(
                $resolvedParameters['reference_prefix'],
                $resolvedParameters['reference']
            ),
            file_get_contents($resolvedParameters['processing_file']->getRealPath())
        );

        // Keep media informations in database
        $media = new Media();

        $media
            ->setSource($resolvedParameters['source'])
            ->setIpSource($resolvedParameters['ip_source'])
            ->setReference($resolvedParameters['reference'])
            ->setReferencePrefix($resolvedParameters['reference_prefix'])
            ->setExtension($resolvedParameters['extension'])
            ->setProviderServiceName($resolvedParameters['storage_provider'])
            ->setName($resolvedParameters['name'])
            ->setDescription($resolvedParameters['description'])
            ->setSize($resolvedParameters['size'])
            ->setMimeType($resolvedParameters['mime_type'])
            ->setMetadata(array_merge_recursive(
                $resolvedParameters['metadata'],
                $this
                    ->guessMetadataExtractor($resolvedParameters['mime_type'])
                    ->extract($resolvedParameters['processing_file']->getRealPath())
            ))
        ;

        $this->add($media);

        // Remove the media once the provider has well stored it.
        unlink($resolvedParameters['processing_file']->getRealPath());
        $resolvedParameters['processing_file'] = null;

        return $media;
    }

    /**
     * Delete mediaRaw
     *
     * @param string $reference
     */
    public function deleteMedia($reference)
    {
        $media = $this->retrieveMedia($reference);
        $storageProvider = $this->getFilesystemMap()->get($media->getProviderServiceName());
        $storageProvider->delete(
            $this->buildStorageKey(
                $media->getReferencePrefix(),
                $media->getReference()
            )
        );
        $this->delete($media);
    }

    /**
     * transform a given Media to a ResponseMedia based on given parameters
     *
     * @param Media $media
     * @param array $options
     * @return ResponseMedia
     */
    public function transform(Media $media, $options)
    {
        $mediaTransformer = $this->guessMediaTransformer($options['format']);

        return $mediaTransformer->transform(
            $this->getFilesystemMap()->get($media->getProviderServiceName()),
            $media,
            array_merge(
                $options,
                array(
                    'storage_key' => $this->buildStorageKey(
                        $media->getReferencePrefix(),
                        $media->getReference()
                    )
                )
            )
        );
    }

    /**
     * Get media public uri
     *
     * @param Media $media
     *
     * @return string
     */
    public function getMediaPublicUri(Media $media)
    {
        return sprintf('%s/media/%s',
            $this->getConfiguration('api_public_endpoint'),
            $media->getReference()
        );
    }
}
