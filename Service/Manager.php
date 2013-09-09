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
use Gaufrette\Filesystem;

class Manager
{
    protected $entityManager;
    protected $storageMappers = array();
    protected $defaultStorePath;

    /**
     * Constructor
     *
     * @param Doctrine\ORM\EntityManager $entityManager
     * @param string defaultStorePath
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
        $this->storageMappers[$storageMapper->getId()] = $storageMapper;
    }

    /**
     * Get storage provider
     *
     * @param string $storageMapperId
     * @return Gaufrette\Filesystem The storage provider.
     */
    public function getStorageProvider($storageMapperId)
    {
        if(!isset($this->storageMappers[$storageMapperId])) {
            throw new UndefinedStorageMapperException($storageMapperId);
        }

        $mapper = $this->storageMappers[$storageMapperId];

        return $mapper->getStorageProvider();
    }

    /**
     * Get Entity Manager
     *
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
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
     * @return string
     */
    public function addMedia(UploadedFile $mediaRaw)
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

        // Store the media at the default path
        $mediaRaw->move($this->getDefaultStorePath(), $reference);
        $defaultMediaPath = sprintf('%s/%s', $this->getDefaultStorePath(), $reference);

        $storageMapper = $this->guessStorageMapper($defaultMediaPath);
        $storageMapper->getStorageProvider()->write(
            $reference,
            file_get_contents($defaultMediaPath)
        );

        // Remove the media once the provider is well guess and used.
        unlink($defaultMediaPath);

        $media = new Media();
        $media->setStorageMapperId($storageMapper->getId());
        $media->setName($mediaRaw->getClientOriginalName());
        $media->setSize($mediaRaw->getClientSize());
        $media->setContentType($mediaRaw->getClientMimeType());
        $media->setReference($reference);
        $this->getEntityManager()->persist($media);
        $this->getEntityManager()->flush();

        return $reference;
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
        $storageProvider = $this->getStorageProvider($media->getStorageMapperId());
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
        return md5(sprintf("%s%s%s",
            $mediaRaw->getClientMimeType(),
            $mediaRaw->getClientOriginalName(),
            $mediaRaw->getClientSize()
        ));
    }

    /**
     * Guess and retrieve the good storage mapper for a mediaRaw.
     *
     * @param string $mediaPath
     * @return StorageMapperInterface The storage mapper.
     * @throw NoMatchedStorageMapperException
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
