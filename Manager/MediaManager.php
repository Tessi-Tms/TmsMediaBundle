<?php

namespace Tms\Bundle\MediaBundle\Manager;

use Doctrine\ORM\EntityManager;
use Gaufrette\FilesystemMap;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
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
     * Setup parameters.
     *
     * @param OptionsResolverInterface $resolver.
     * @return array
     */
    protected function setupParameters(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'media',
                'working_directory',
                'cache_directory',
                'default_storage_provider',
                'api_public_endpoint',
            ))
            ->setDefaults(array(
                'processing_file'    => null,
                'source'             => null,
                'name'               => null,
                'description'        => null,
                'metadata'           => array(),
                'mime_type'          => null,
                'extension'          => null,
                'size'               => null,
                'reference'          => null,
            ))
            ->setAllowedTypes(array(
                'media'                    => array('Symfony\Component\HttpFoundation\File\UploadedFile'),
                'processing_file'          => array('Symfony\Component\HttpFoundation\File\File'),
                'working_directory'        => array('string'),
                'cache_directory'          => array('string'),
                'default_storage_provider' => array('string'),
                'api_public_endpoint'      => array('string'),
                'source'                   => array('null', 'string'),
                'name'                     => array('null', 'string'),
                'description'              => array('null', 'string'),
                'metadata'                 => array('array'),
                'mime_type'                => array('null', 'string'),
                'extension'                => array('null', 'string'),
                'size'                     => array('null', 'integer'),
                'reference'                => array('null', 'string'),
            ))
            ->setNormalizers(array(
                'name'            => function(Options $options, $value) {
                    if (null !== $value) {
                        return $value;
                    }

                    return $options['media']->getClientOriginalName();
                },
                'description'     => function(Options $options, $value) {
                    if (null !== $value) {
                        return $value;
                    }

                    return $options['media']->getClientOriginalName();
                },
                'mime_type'       => function(Options $options, $value) {
                    return $options['media']->getMimeType();
                },
                'extension'       => function(Options $options, $value) {
                    return $options['media']->guessExtension();
                },
                'processing_file' => function(Options $options, $value) {
                    return $options['media']->move(
                        $options['cache_directory'],
                        uniqid('tmp_media_')
                    );
                },
                'size'            => function(Options $options, $value) {
                    return $options['processing_file']->getSize();
                },
                'reference'       => function(Options $options, $value) {
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
            ))
        ;
    }

    /**
     * Constructor
     *
     * @param array                         $configuration
     * @param EntityManager                 $entityManager
     * @param ContainerAwareEventDispatcher $eventDispatcher
     * @param FilesystemMap                 $filesystemMap
     */
    public function __construct(
        array $configuration = array(),
        EntityManager $entityManager,
        ContainerAwareEventDispatcher $eventDispatcher,
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
    public function add($entity)
    {
        $this->getEventDispatcher()->dispatch(
            MediaEvents::PRE_CREATE,
            new MediaEvent($entity)
        );

        parent::add($entity);

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
     */
    public function addMetadataExtractor(MetadataExtractorInterface $metadataExtractor)
    {
        $this->metadataExtractors[] = $metadataExtractor;
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
     */
    public function addMediaTransformer(MediaTransformerInterface $mediaTransformer)
    {
        $this->mediaTransformers[] = $mediaTransformer;
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

        $provider = $this->filesystemMap->get($resolvedParameters['default_storage_provider']);
        var_dump($resolvedParameters, $provider);die;

        $this->getStorageProvider()->write(
            $resolvedParameters['reference'],
            file_get_contents($resolvedParameters['processing_file']->getRealPath())
        );

        //$providerServiceName = $storageMapper->getStorageProviderServiceName();


        // Keep media informations in database
        $media = new Media();

        $media->setSource($resolvedParameters['source']);
        $media->setReference($resolvedParameters['reference']);
        $media->setExtension($resolvedParameters['extension']);
        $media->setProviderServiceName($providerServiceName);
        $media->setName($resolvedParameters['name']);
        $media->setDescription($resolvedParameters['description']);
        $media->setSize($resolvedParameters['size']);
        $media->setMimeType($resolvedParameters['mime_type']);

        $media->setMetadata(array_merge_recursive(
            $resolvedParameters['metadata'],
            $this
                ->guessMetadataExtractor($resolvedParameters['mime_type'])
                ->extract($resolvedParameters['processing_file']->getRealPath())
        ));

        $this->add($media);

        // Remove the media if a provider was well guess and used, and the media entity stored.
        unlink($resolvedParameters['processing_file']->getRealPath());

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
        $storageProvider = $this->getStorageProvider($media->getProviderServiceName());
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
            $this->getStorageProvider($media->getProviderServiceName()),
            $media,
            $options
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
