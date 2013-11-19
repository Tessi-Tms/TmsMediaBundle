<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Media\Transformer;

use Tms\Bundle\MediaBundle\Entity\Media;
use Gaufrette\Filesystem;
use Tms\Bundle\MediaBundle\Media\ResponseMedia;

interface MediaTransformerInterface
{
    /**
     * Check the format
     *
     * @param string $format
     * @return boolean
     */
    public function checkFormat($format);

    /**
     * transform
     *
     * @param Filesystem $storageProvider
     * @param Media $media
     * @param array $options
     * @return ResponseMedia
     */
    public function transform(Filesystem $storageProvider, Media $media, $options = array());
}
