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

class MaxSizeRule extends AbstractSizeRule
{
    /**
     * {@inheritdoc}
     */
    function check(UploadedFile $file)
    {
        if($file->getClientSize() > ($this->toByteSize($this->getRuleArguments()))) {
            return false;
        }

        return true;
    }

}
