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
    function check(array $parameters)
    {
        if (!isset($parameters['size'])) {
            return false;
        }

        if ($parameters['size'] > self::convertToBytes($this->getRuleArguments())) {
            return false;
        }

        return true;
    }

}
