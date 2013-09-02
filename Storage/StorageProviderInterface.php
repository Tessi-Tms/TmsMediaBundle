<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Storage;

interface StorageProviderInterface
{
    /**
     * Check rules
     *
     * @param File $media
     */
    public function checkRules($media)
    {
        //TODO
    }
}
