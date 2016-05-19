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

interface RuleInterface
{
    /**
     * Check the rule for a file.
     *
     * @param array $parameters
     * @return boolean
     */
    public function check(array $parameters);
}
