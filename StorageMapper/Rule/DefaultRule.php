<?php

/**
 *
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\StorageMapper\Rule;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefaultRule extends AbstractRule
{
    /**
     * {@inheritdoc}
     */
    function check(array $parameters)
    {
        return true;
    }
}
