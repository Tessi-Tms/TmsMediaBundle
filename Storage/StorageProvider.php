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
use Gaufrette\Filesystem;
use Tms\Bundle\MediaBundle\Storage\Rule\RuleInterface;

class StorageProvider implements StorageProviderInterface
{
    protected $rules = array();

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
    public function checkRules(File $media)
    {
        foreach($this->rules as $rule) {
            if (!$rule->check($media)) {
                return false;
            }
        }

        return true;
    }
}
