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

abstract class AbstractRule implements RuleInterface
{
    protected $ruleArguments;

    public function __construct($ruleArguments)
    {
        $this->ruleArguments = $ruleArguments;
    }

    public function getRuleArguments()
    {
        return $this->ruleArguments;
    }
}
