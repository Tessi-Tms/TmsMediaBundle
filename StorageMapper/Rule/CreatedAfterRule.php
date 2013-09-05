<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\StorageMapper\Rule;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreatedAfterRule extends AbstractRule
{
    /**
     * {@inheritdoc}
     */
    function check(UploadedFile $file)
    {
        $now = new \DateTime();
        if($this->getRuleArguments() > $now) {
            return true;
        }

        return false;
    }

}
