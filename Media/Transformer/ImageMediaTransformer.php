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
use Gregwar\ImageBundle\Services\ImageHandling as ImageManager;

class ImageMediaTransformer extends AbstractMediaTransformer
{
    protected $imageManager;

    /**
     * Constructor
     *
     * @param $cacheManager;
     * @param Exporter $exporter
     */
    public function __construct(ImageManager $imageManager)
    {
        parent::__construct(null);

        $this->imageManager = $imageManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array('jpg', 'jpeg', 'png', 'gif', 'tiff', 'vnd', 'svg');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableParameters()
    {
        return array('width', 'height', 'rotate', 'scale', 'greyscale');
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, $format, $parameters = array())
    {
        $original = $storageProvider->read($media->getReference());

        if($format === $media->getExtension()) {
            $responseMedia = new ResponseMedia($media);
            $responseMedia->setContent($original);

            return $responseMedia;
        }

        
        die('good image');
        return $responseMedia;
    }
}
