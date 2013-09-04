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

class MimeTypesRule extends AbstractRule
{
    /**
     * {@inheritdoc}
     */
    function check(UploadedFile $file)
    {
        return in_array($file->getMimeType(), $this->getRuleArguments());
    }
}
