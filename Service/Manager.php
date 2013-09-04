<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Service;

use Symfony\Component\HttpFoundation\File\File;
use Tms\Bundle\MediaBundle\Entity\Media;
use Tms\Bundle\MediaBundle\Storage\StorageProviderInterface;
use Tms\Bundle\MediaBundle\Exception\NoMatchedStorageProviderException;
use Doctrine\ORM\EntityManager;
use Gaufrette\Filesystem;

class Manager
{
    protected $entityManager;
    protected $storageProviders = array();

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
     * Add storage provider
     *
     * @param StorageProviderInterface $provider
     */
    public function addStorageProvider(StorageProviderInterface $provider)
    {
        $this->storageProviders[] = $provider;
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
     * Get Store Manager
     *
     * @return \Gaufrette\Filesystem
     */
    /*public function getStoreManager()
    {
        return $this->storeManager;
    }*/

    /**
     * Add Media
     *
     * @param File $mediaRaw
     */
    public function addMedia($mediaRaw)
    {
        if($this->guessStorageProvider($mediaRaw)) {
            // 1] Enregistrer le media via store manager (gaufrette)
            /*$this->getStoreManager()->write(
                $mediaRaw->getClientOriginalName(),
                $mediaRaw
            );*/
            // 2] Ajouter les informations du media en base
            $media = new Media();
            $ref = $this->generateMediaReference($mediaRaw);
            $media->setName($mediaRaw->getClientOriginalName());
            $media->setSize($mediaRaw->getClientSize());
            $media->setContentType($mediaRaw->getMimeType());
            $media->setReference($ref);
            //var_dump($media);die;

            $this->getEntityManager()->persist($media);
            $this->getEntityManager()->flush();
            die('Sauvegarde effectué');
        }

    }

    /**
     * Retrieve Media
     *
     * @param string $id
     * @return array
     */
    public function retrieveMedia($id)
    {
    }

    /**
     * Delete Media
     *
     * @param string $id
     */
    public function deleteMedia($id)
    {
    }

    /**
     * Generate a unique rereference for a mediaRaw
     *
     * @param File $imediaRaw
     *
     * @return string
     */
    public function generateMediaReference(File $mediaRaw)
    {
        $fileName = sprintf('%s.%s.%s', 
            $mediaRaw->getClientOriginalExtension(), 
            $mediaRaw->getMimeType(),
            uniqid())
        ;

        return $fileName;
    }

    /**
     * Guess and retrieve the good storage provider for a mediaRaw.
     *
     * @param File $mediaRaw
     *
     * @return StorageProviderInterface The storage provider.
     *
     * @throw NoMatchedStorageProviderException
     */
    public function guessStorageProvider(File $mediaRaw)
    {
        foreach ($this->storageProviders as $storageProvider) {
            if ($storageProvider->checkRules($mediaRaw)) {
                return $storageProvider;
            }
        }

        throw new NoMatchedStorageProviderException();
    }
}
