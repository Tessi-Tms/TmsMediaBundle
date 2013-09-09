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
    protected $storageProvider;
    protected $id;
    protected $rules = array();

    public function __construct(Filesystem $storageProvider, $id)
    {
        $this->storageProvider = $storageProvider;
        $this->id = $id;
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
    public function getId()
    {
        return $this->id;
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
    public function checkRules($mediaPath)
    {
        foreach($this->rules as $rule) {
            if (!$rule->check($mediaPath)) {
                return false;
            }
        }

        return true;
    }
}
