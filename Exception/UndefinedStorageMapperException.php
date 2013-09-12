<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */
namespace Tms\Bundle\MediaBundle\Exception;

class UndefinedStorageMapperException extends \Exception
{
    /**
     * The constructor.
     */
    public function __construct($id)
    {
        parent::__construct(sprintf('The storage mapper %s is undefined.', $id));
    }
}
