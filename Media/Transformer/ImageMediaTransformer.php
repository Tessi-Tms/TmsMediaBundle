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
    protected $cacheDir;

    /**
     * Constructor
     *
     * @param ImageManager $imageManager
     * @param string $cacheDir
     */
    public function __construct(ImageManager $imageManager, $cacheDir)
    {
        parent::__construct(null);

        $this->imageManager = $imageManager;
        $this->cacheDir = $cacheDir;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array('jpg', 'jpeg', 'png', 'gif');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableParameters()
    {
        return array('width', 'height', 'scale', 'grayscale', 'maxwidth', 'maxheight', 'minwidth', 'minheight');
    }

    protected static function getMimeType($type)
    {
        $mimeTypeMap = array(
            'jpg'   => 'image/jpg',
            'jpeg'  => 'image/jpeg',
            'png'   => 'image/png',
            'gif'   => 'image/gif'
        );

        return $mimeTypeMap[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, $format, $parameters = array())
    {
        $responseMedia = new ResponseMedia();
        $responseMedia->setContentType(self::getMimeType($format));

        $imageCacheName = sprintf('%s_%s.%s',
            $media->getReference(),
            sprintf("%u", crc32(serialize($parameters))),
            $format
        );
        $imageCachePath = sprintf('%s%s', $this->cacheDir, $imageCacheName);

        if(file_exists($imageCachePath)) {
            $responseMedia->setContent(file_get_contents($imageCachePath));

            return $responseMedia;
        }

        $originalContent = $storageProvider->read($media->getReference());

        if($format === $media->getExtension() && count($parameters) == 0 ) {
            $responseMedia = new ResponseMedia($media);
            $responseMedia->setContent($originalContent);

            return $responseMedia;
        }

        $imageSourceCachePath = sprintf('%s%s.%s',
            $this->cacheDir,
            $media->getReference(),
            $media->getExtension()
        );

        file_put_contents($imageSourceCachePath, $originalContent);
        $image = $this->imageManager->open($imageSourceCachePath);

        if(isset($parameters['width']) || isset($parameters['height'])) {
            $w = isset($parameters['width']) ? $parameters['width'] : null;
            $h = isset($parameters['height']) ? $parameters['height'] : null;

            $image->forceResize($w, $h);
        }

        if (
                isset($parameters['maxheight']) ||
                isset($parameters['maxwidth'])  ||
                isset($parameters['minheight']) ||
                isset($parameters['minwidth'])
            ) {
            $h = $media->getMetadata('height');
            $w = $media->getMetadata('width');

            if  (isset($parameters['minheight']) && $parameters['minheight'] > $h) {
                $w = $w * $parameters['minheight'] / $h;
                $h = $parameters['minheight'];
            }

            if  (isset($parameters['minwidth']) && $parameters['minwidth'] > $w) {
                $h = $h * $parameters['minwidth'] / $w;
                $w = $parameters['minwidth'];
            }

            if  (isset($parameters['maxheight']) && $parameters['maxheight'] < $h) {
                    $w = $w * $parameters['maxheight'] / $h;
                    $h = $parameters['maxheight'];
            }

            if  (isset($parameters['maxwidth']) && $parameters['maxwidth'] < $w) {
                $h = $h * $parameters['maxwidth'] / $w;
                $w = $parameters['maxwidth'];
            }

            $image->forceResize($w, $h);
        }

        if(isset($parameters['grayscale'])) {
            $image->grayscale();
        }

        if(isset($parameters['scale'])) {
            $w = $media->getMetadata('width') * $parameters['scale'] / 100;

            $image->scaleResize($w);
        }

        $image->save($imageCachePath, $format, 95);

        $responseMedia->setContent(file_get_contents($imageCachePath));

        return $responseMedia;
    }
}
