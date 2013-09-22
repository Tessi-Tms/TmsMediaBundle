<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tms\Bundle\MediaBundle\Entity\Media;
use Tms\Bundle\MediaBundle\Media\ResponseMedia;
use Tms\Bundle\MediaBundle\StorageMapper\StorageMapperInterface;
use Tms\Bundle\MediaBundle\MetadataExtractor\MetadataExtractorInterface;
use Tms\Bundle\MediaBundle\Media\Transformer\MediaTransformerInterface;
use Tms\Bundle\MediaBundle\Exception\MediaAlreadyExistException;
use Tms\Bundle\MediaBundle\Exception\NoMatchedStorageMapperException;
use Tms\Bundle\MediaBundle\Exception\UndefinedStorageMapperException;
use Tms\Bundle\MediaBundle\Exception\MediaNotFoundException;
use Tms\Bundle\MediaBundle\Exception\NoMatchedTransformerException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Gaufrette\Filesystem;

class Manager
{
    protected $entityManager;
    protected $defaultStorePath;
    protected $storageMappers = array();
    protected $metadataExtractors = array();
    protected $mediaTransformers = array();

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     * @param string $defaultStorePath
     */
    public function __construct(EntityManager $entityManager, $defaultStorePath)
    {
        $this->entityManager = $entityManager;
        $this->defaultStorePath = $defaultStorePath;
    }

    /**
     * Add storage mapper
     *
     * @param StorageMapperInterface $storageMapper
     */
    public function addStorageMapper(StorageMapperInterface $storageMapper)
    {
        $this->storageMappers[] = $storageMapper;
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
     * Add media transformer
     *
     * @param MediaTransformerInterface $mediaTransformer
     */
    public function addMediaTransformer(MediaTransformerInterface $mediaTransformer)
    {
        $this->mediaTransformers[] = $mediaTransformer;
    }

    /**
     * Get Entity Manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Get storage provider
     *
     * @param string providerServiceName
     * @return Gaufrette\Filesystem The storage provider.
     * @throw UndefinedStorageMapperException
     */
    public function getStorageProvider($providerServiceName)
    {
        foreach($this->storageMappers as $storageMapper) {
            if($providerServiceName == $storageMapper->getStorageProviderServiceName()) {
                return $storageMapper->getStorageProvider();
            }
        }

        throw new UndefinedStorageMapperException();
    }

    /**
     * Get default store path
     *
     * @return string
     */
    public function getDefaultStorePath()
    {
        return $this->defaultStorePath;
    }

    /**
     * Add Media
     *
     * @param UploadedFile $mediaRaw
     * @param string $source
     * @param string $name
     * @param string $description
     * @return Media
     */
    public function addMedia(UploadedFile $mediaRaw, $source = null, $name = null, $description = null)
    {
        $reference = $this->generateMediaReference($source, $mediaRaw);

        $media = $this
            ->getEntityManager()
            ->getRepository('TmsMediaBundle:Media')
            ->findOneBy(array('reference' => $reference))
        ;

        if($media) {
            throw new MediaAlreadyExistException();
        }

        // Keep media information before handle the file
        $mimeType = $mediaRaw->getMimeType();
        $extension = $mediaRaw->guessExtension();
        $name = is_null($name) ? $mediaRaw->getClientOriginalName() : $name;
        $description = is_null($description) ? $mediaRaw->getClientOriginalName() : $description;

        // Store the media at the default path
        $mediaRaw->move($this->getDefaultStorePath(), $reference);
        $defaultMediaPath = sprintf('%s/%s', $this->getDefaultStorePath(), $reference);

        // Guess a storage provider and use it to store the media
        $storageMapper = $this->guessStorageMapper($defaultMediaPath);
        $storageMapper->getStorageProvider()->write(
            $reference,
            file_get_contents($defaultMediaPath)
        );
        $providerServiceName = $storageMapper->getStorageProviderServiceName();

        // Keep media informations in database
        $media = new Media();

        $media->setSource($source);
        $media->setReference($reference);
        $media->setExtension($extension);
        $media->setProviderServiceName($providerServiceName);
        $media->setName($name);
        $media->setDescription($description);
        $media->setSize(filesize($defaultMediaPath));
        $media->setMimeType($mimeType);

        $media->setMetadata($this
            ->guessMetadataExtractor($mimeType)
            ->extract($defaultMediaPath)
        );

        $this->getEntityManager()->persist($media);
        $this->getEntityManager()->flush();

        // Remove the media if a provider was well guess and used, and the media entity stored.
        unlink($defaultMediaPath);

        return $media;
    }

    /**
     * Retrieve mediaRaw
     *
     * @param string $reference
     * @return array The media
     */
    public function retrieveMedia($reference)
    {
        $media = $this
            ->getEntityManager()
            ->getRepository('TmsMediaBundle:Media')
            ->findOneBy(array('reference' => $reference))
        ;

        if(!$media) {
            throw new MediaNotFoundException($reference);
        }

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
        $storageProvider->delete($media->getReference());
        $this->entityManager->remove($media);
        $this->entityManager->flush();
    }

    /**
     * Generate a unique rereference for a mediaRaw
     *
     * @param string $source
     * @param UploadedFile $mediaRaw
     *
     * @return string
     */
    public function generateMediaReference($source, UploadedFile $mediaRaw)
    {
        $now = new \DateTime();

        return sprintf('%s-%s-%s',
            sprintf("%u", crc32($source)),
            $now->format('U'),
            md5(sprintf("%s%s%s",
              $mediaRaw->getClientMimeType(),
              $mediaRaw->getClientOriginalName(),
              $mediaRaw->getClientSize()
            ))
        );
    }

    /**
     * Guess a storage mapper based on the given mediaRaw.
     *
     * @param string $mediaPath
     * @return StorageMapperInterface
     * @throw NoMatchedStorageProviderException
     */
    protected function guessStorageMapper($mediaPath)
    {
        foreach ($this->storageMappers as $storageMapper) {
            if ($storageMapper->checkRules($mediaPath)) {
                return $storageMapper;
            }
        }

        throw new NoMatchedStorageMapperException();
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
     * transform a given Media to a ResponseMedia based on given parameters
     *
     * @param Media $media
     * @param string $format
     * @param array $parameters
     * @return ResponseMedia
     */
    public function transform(Media $media, $format, $parameters)
    {
        $mediaTransformer = $this->guessMediaTransformer($format);

        return $mediaTransformer->transform(
            $this->getStorageProvider($media->getProviderServiceName()),
            $media,
            $format,
            $parameters
        );
    }
}
