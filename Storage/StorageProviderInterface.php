<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Storage;

use Symfony\Component\HttpFoundation\File\File;
use Tms\Bundle\MediaBundle\Storage\Rule\RuleInterface;

interface StorageProviderInterface
{
    /**
     * Check the rules.
     *
     * @param File $media
     */
    function checkRules(File $media);

    /**
     * Add a rule to the provider.
     *
     * @param RuleInterface $rule
     */
    function addRule(RuleInterface $rule);
}
