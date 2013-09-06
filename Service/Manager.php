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
use Tms\Bundle\MediaBundle\Exception\NoMatchedStorageProviderException;
use Tms\Bundle\MediaBundle\Exception\MediaNotFoundException;
use Doctrine\ORM\EntityManager;
use Gaufrette\Filesystem;

class Manager
{
    protected $entityManager;
    protected $storageMappers = array();

    /**
     * Constructor
     *
     * @param Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Add storage mapper
     *
     * @param StorageMapperInterface $storageMapper
     */
    public function addStorageMapper(StorageMapperInterface $storageMapper)
    {
        $this->storageMappers[$storageMapper->getStorageProviderServiceName()] = $storageMapper;
    }

    /**
     * Get storage provider
     *
     * @param string providerServiceName
     * @return Gaufrette\Filesystem The storage provider.
     */
    public function getStorageProvider($providerServiceName)
    {
        $mapper = $this->storageMappers[$providerServiceName];

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
     * Add Media
     *
     * @param UploadedFile $mediaRaw
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

        $storageMapper = $this->guessStorageMapper($mediaRaw);
        $storageMapper->getStorageProvider()->write($reference, $mediaRaw);

        $media = new Media();
        $media->setProviderServiceName($storageMapper->getStorageProviderServiceName());
        $media->setName($mediaRaw->getClientOriginalName());
        $media->setSize($mediaRaw->getClientSize());
        $media->setContentType($mediaRaw->getClientMimeType());
        $media->setReference($reference);
        $this->getEntityManager()->persist($media);
        $this->getEntityManager()->flush();
    }

    /**
     * Retrieve mediaRaw
     *
     * @param string $reference
     * @return array The media
     */
    public function retrieveMedia($reference, $raw = false)
    {
        $media = $this
            ->getEntityManager()
            ->getRepository('TmsMediaBundle:Media')
            ->findOneBy(array('reference' => $reference))
        ;

        if(!$media) {
            throw new MediaNotFoundException($reference);
        }

        if($raw) {
            $storageProvider = $this->getStorageProvider($media->getProviderServiceName());

            return $storageProvider->read($media->getReference());
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
        return md5(sprintf("%s%s%s",
            $mediaRaw->getClientMimeType(),
            $mediaRaw->getClientOriginalName(),
            $mediaRaw->getClientSize()
        ));
    }

    /**
     * Guess and retrieve the good storage mapper for a mediaRaw.
     *
     * @param UploadedFile $mediaRaw
     *
     * @return StorageMapperInterface The storage mapper.
     *
     * @throw NoMatchedStorageProviderException
     */
    public function guessStorageMapper(UploadedFile $mediaRaw)
    {
        foreach ($this->storageMappers as $storageMapper) {
            if ($storageMapper->checkRules($mediaRaw)) {
                return $storageMapper;
            }
        }

        throw new NoMatchedStorageProviderException();
    }
}
