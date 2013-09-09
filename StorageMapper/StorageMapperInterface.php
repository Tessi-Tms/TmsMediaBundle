<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\StorageMapper;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tms\Bundle\MediaBundle\StorageMapper\Rule\RuleInterface;

interface StorageMapperInterface
{
    /**
     * Get Storage provider
     *
     * @return Gaufrette\Filesystem
     */
    public function getStorageProvider();

    /**
     * Get Storage mapper identifier
     *
     * @return string
     */
    public function getId();

    /**
     * Add a rule to the provider.
     *
     * @param RuleInterface $rule
     */
    public function addRule(RuleInterface $rule);

    /**
     * Check the rules.
     *
     * @param string $mediaPath
     */
    public function checkRules($mediaPath);
}
