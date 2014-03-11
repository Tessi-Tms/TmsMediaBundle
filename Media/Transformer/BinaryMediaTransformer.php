<?php

/**
 *
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Julien ANDRE <julien.andre1907@gmail.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Media\Transformer;

use Gaufrette\Filesystem;

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
        $processed = parent::process($storageProvider, $media, $options);
        return $this->getBinary($processed);
    }

    /*
    * get the binary of a processed image
    */
    private function getBinary($imageMedia)
    {
        return $imageMedia;
    }
}
