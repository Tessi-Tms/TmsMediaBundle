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
     */
    public function __construct(\Doctrine\ORM\EntityManager $entityManager $entityManager, $storeManager)
    {
        $this->entityManager = $entityManager;
        $this->storeManager = $storeManagers;
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
     * @return 
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Add Media
     *
     * @param File $media
     */
    public function addMedia($media)
    {
        // 1] Enregistrer le media via store manager (gaufrette)
        $this->getStoreManager()->write(
            $media->getClientOriginalName(),
            $media
        );

        // 2] Ajouter les information du media en base
        //Todo

        
    }

    /**
     * Get Media
     */
    public function getMedia()
    {
    }

    public function deleteMedia()
    {
    }
}


