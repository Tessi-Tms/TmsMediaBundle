<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Storage\Rule;

use Symfony\Component\HttpFoundation\File\File;

class MinSizeRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    function check(File $media)
    {
        return true;
    }
}
