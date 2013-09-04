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
     * Check the rules.
     *
     * @param UploadedFile $media
     */
    public function checkRules(UploadedFile $media);

    /**
     * Add a rule to the provider.
     *
     * @param RuleInterface $rule
     */
    public function addRule(RuleInterface $rule);

    /**
     * Get Storage provider service name
     *
     * @return Gaufrette\Filesystem
     */
    public function getStorageProviderServiceName();

    /**
     * Get Storage provider
     *
     * @return Gaufrette\Filesystem
     */
    public function getStorageProvider();
}
