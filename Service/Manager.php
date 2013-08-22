<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Service;

use Tms\Bundle\MediaBundle\Entity\Media;
use Tms\Bundle\MediaBundle\Util\Inflector;

class Manager
{
    protected $entityManager;
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Gaufrette\Filesystem $storeManager
     */
    public function __construct(\Doctrine\ORM\EntityManager $entityManager, \Gaufrette\Filesystem $storeManager)
    {
        $this->entityManager = $entityManager;
        $this->storeManager = $storeManager;
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
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Add Media
     *
     * @param File $mediaRaw
     * @throw UnavailableMediaException
     */
    public function addMedia($mediaRaw)
    {
        // 1] Enregistrer le media via store manager (gaufrette)
        $this->getStoreManager()->write(
            $mediaRaw->getClientOriginalName(),
            $mediaRaw
        );

        // 2] Ajouter les informations du media en base
        $media = new Media();
        $media->setName(trim($mediaRaw->getClientOriginalName()));
        //$media->setDescription();
        //$media->setProviderServiceName();
        //$media->setProviderData();
        //$media->setWidth();
        //$media->setHeight();
        $media->setSize($mediaRaw->getClientSize());
        $media->setContentType(trim($mediaRaw->getMimeType()));
        var_dump($media);
        die('Every thing is done');

        $this->getEntityManager()->persist($media);
        $this->getEntityManager()->flush();
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
     * Generate media id
     *
     * @param File $imediaRaw
     * @return string
     */
    public function generateMediaId($mediaRaw)
    {
        //TODO generateMediaId with extension - mimeType - size
        $fileName = sprintf('%s/%s.%s', 
            $mediaRaw->getClientOriginalExtension(), 
            $mediaRaw->getMimeType(),
            $mediaRaw->getClientSize())
        ;

        return $fileName;
    }

    /**
     * Guess provider service
     *
     * @param File $mediaRaw
     * @return string
     */
    public function guessProviderService($mediaRaw)
    {
        //TODO return providerName according giving provider
    }
}
