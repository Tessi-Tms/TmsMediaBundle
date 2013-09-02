<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Storage;

use \Gaufrette\Filesystem;

class StorageProvider implements StorageProviderInterface
{
    private $rules = array();
    private $providers = array();

    /**
     * Add provider rules
     *
     * @param array $providers
     * @param array $rules
     */
    public function addProviderRules($providers, $rules)
    {
        //TODO add rules to a specific provider
    }
}
