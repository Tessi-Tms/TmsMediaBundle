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
use Gaufrette\Filesystem;
use Tms\Bundle\MediaBundle\StorageMapper\Rule\RuleInterface;

class StorageMapper implements StorageMapperInterface
{
    protected $rules = array();
    protected $storageProvider;
    protected $storageProviderServiceName;

    public function __construct(Filesystem $storageProvider, $storageProviderServiceName)
    {
        $this->storageProvider = $storageProvider;
        $this->storageProviderServiceName = $storageProviderServiceName;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageProviderServiceName()
    {
        return $this->storageProviderServiceName;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageProvider()
    {
        return $this->storageProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function addRule(RuleInterface $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function checkRules(UploadedFile $media)
    {
        foreach($this->rules as $rule) {
            if (!$rule->check($media)) {
                return false;
            }
        }

        return true;
    }
}
