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
    function check(array $parameters)
    {
        if (!isset($parameters['mime_type']) || null === $this->getRuleArguments()) {
            return false;
        }

        return in_array($parameters['mime_type'], $this->getRuleArguments());
    }
}
