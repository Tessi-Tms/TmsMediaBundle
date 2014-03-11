<?php

/**
 *
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Julien ANDRE <julien.andre1907@gmail.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Media\Transformer;

class BinaryMediaTransformer extends ImageMediaTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array('bin');
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, array $options = array())
    {
        $processed = parent::process($storageProvider, $media, $options)
        return $this->getBinary($processed);
    }

    /*
    * 
    */
    private function getBinary($imageMedia)
    {
        return true;
    }
}
