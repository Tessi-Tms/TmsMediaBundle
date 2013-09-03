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

interface RuleInterface
{
    /**
     * Check the rule for a media.
     *
     * @param File $media
     */
    function check(File $media);
}
