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
use Tms\Bundle\MediaBundle\StorageMapper\StorageMapperInterface;
use Tms\Bundle\MediaBundle\Exception\MediaAlreadyExistException;
use Tms\Bundle\MediaBundle\Exception\NoMatchedStorageMapperException;
use Tms\Bundle\MediaBundle\Exception\UndefinedStorageMapperException;
use Tms\Bundle\MediaBundle\Exception\MediaNotFoundException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Gaufrette\Filesystem;

class Manager
{
    protected $entityManager;
    protected $container;
    protected $storageMappers = array();
    protected $defaultStorePath;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     * @param ContainerInterface $container
     * @param string $defaultStorePath
     */
    public function __construct(EntityManager $entityManager, ContainerInterface $container, $defaultStorePath)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
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
     * Get Entity Manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Get Container
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get storage provider
     *
     * @param string providerServiceName
     * @return Gaufrette\Filesystem The storage provider.
     */
    public function getStorageProvider($providerServiceName)
    {
        return $this->getContainer()->get($providerServiceName);
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
     * Is Image
     *
     * @param string $mimeType
     * @return boolean
     */
    public function isImage($mimeType)
    {
        return in_array($mimeType, array(
            'image/gif',
            'image/jpeg',
            'image/png',
            'image/tiff',
            'image/vnd.microsoft.icon',
            'image/svg+xml',
        ));
    }

    /**
     * Add Media
     *
     * @param UploadedFile $mediaRaw
     * @param string $name
     * @param string $description
     * @return Media
     */
    public function addMedia(UploadedFile $mediaRaw, $name = null, $description = null)
    {
        $reference = $this->generateMediaReference($mediaRaw);

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
        $name = is_null($name) ? $mediaRaw->getClientOriginalName() : $name;
        $description = is_null($description) ? $mediaRaw->getClientOriginalName() : $description;

        // Store the media at the default path
        $mediaRaw->move($this->getDefaultStorePath(), $reference);
        $defaultMediaPath = sprintf('%s/%s', $this->getDefaultStorePath(), $reference);

        $providerServiceName = null;
        try {
            $storageMapper = $this->guessStorageMapper($defaultMediaPath);
            $storageMapper->getStorageProvider()->write(
                $reference,
                file_get_contents($defaultMediaPath)
            );
            $providerServiceName = $storageMapper->getStorageProviderServiceName();
        } catch(NoMatchedStorageProviderException $e) {
            $providerServiceName = 'default';
        }

        $media = new Media();
        $media->setProviderServiceName($providerServiceName);
        $media->setReference($reference);
        $media->setName($name);
        $media->setDescription($description);
        $media->setSize(filesize($defaultMediaPath));
        $media->setMimeType($mimeType);

        if($this->isImage($mimeType)) {
            list($width, $height) = getimagesize($defaultMediaPath);
            $media->setWidth($width);
            $media->setHeight($height);
        }

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
     * @param UploadedFile $mediaRaw
     *
     * @return string
     */
    public function generateMediaReference(UploadedFile $mediaRaw)
    {
        $fileName = sprintf('%s.%s',
            $this->hashFile($mediaRaw),
            $mediaRaw->getClientOriginalExtension()
        );

        return $fileName;
    }

    /**
     * Generate a unique hash based on file content
     *
     * @param UploadedFile $mediaRaw
     *
     * @return string
     */
    public function hashFile(UploadedFile $mediaRaw)
    {
        $now = new \DateTime();

        return sprintf('%s-%s',
            md5($now->format('Ymdhis')),
            md5(sprintf("%s%s%s",
              $mediaRaw->getClientMimeType(),
              $mediaRaw->getClientOriginalName(),
              $mediaRaw->getClientSize()
            ))
        );
    }

    /**
     * Guess and retrieve the good storage mapper for a mediaRaw.
     *
     * @param string $mediaPath
     * @return StorageMapperInterface The storage mapper.
     * @throw NoMatchedStorageProviderException
     */
    public function guessStorageMapper($mediaPath)
    {
        foreach ($this->storageMappers as $storageMapper) {
            if ($storageMapper->checkRules($mediaPath)) {
                return $storageMapper;
            }
        }

        throw new NoMatchedStorageMapperException();
    }
}
