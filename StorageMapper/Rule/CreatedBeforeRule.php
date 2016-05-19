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

class CreatedBeforeRule extends AbstractCreatedRule
{
    /**
     * {@inheritdoc}
     */
    function check(array $parameters)
    {
        if (null === $this->getRuleArguments()) {
            return false;
        }

        $now = new \DateTime();
        $before = self::convertToDateTime($this->getRuleArguments());
        if ($before->format('U') < $now->format('U')) {
            return false;
        }

        return true;
    }
}
