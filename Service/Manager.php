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
    /**
     * Constructor
     */
    public function __construct(\Doctrine\ORM\EntityManager $entityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
     * @return Doctrine\ORM\EntityManager
     */
    public function addMedia()
    {
    }
}


