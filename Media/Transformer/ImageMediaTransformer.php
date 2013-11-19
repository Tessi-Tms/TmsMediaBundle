<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Media\Transformer;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setOptional(array(
            'width',
            'height',
            'scale',
            'grayscale',
            'maxwidth',
            'maxheight',
            'minwidth',
            'minheight'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, $options = array())
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $responseMedia = new ResponseMedia();

        $originalContent = $storageProvider->read($media->getReference());

        if ($options['format'] === $media->getExtension() && count($options) == 1) {
            $responseMedia
                ->setContent($originalContent)
                ->setContentType($media->getMimeType())
                ->setContentLength($media->getSize())
                ->setLastModifiedAt($media->getCreatedAt())
            ;

            return $responseMedia;
        }

        $imageCacheName = sprintf('%s_%s.%s',
            $media->getReference(),
            sprintf("%u", crc32(serialize($options))),
            $options['format']
        );
        $imageCachePath = sprintf('%s%s', $this->cacheDir, $imageCacheName);
        if (file_exists($imageCachePath)) {
            $date = new \DateTime();
            $date->setTimestamp(filemtime($imageCachePath));

            $responseMedia
                ->setContent(file_get_contents($imageCachePath))
                ->setContentType(finfo_file($finfo, $imageCachePath))
                ->setContentLength(filesize($imageCachePath))
                ->setLastModifiedAt($date)
            ;

            return $responseMedia;
        }

        $imageSourceCachePath = sprintf('%s%s.%s',
            $this->cacheDir,
            $media->getReference(),
            $media->getExtension()
        );

        file_put_contents($imageSourceCachePath, $originalContent);
        $image = $this->imageManager->open($imageSourceCachePath);

        if (isset($options['width']) || isset($options['height'])) {
            $w = isset($options['width']) ? $options['width'] : null;
            $h = isset($options['height']) ? $options['height'] : null;

            $image->forceResize($w, $h);
        }

        if (isset($options['maxheight']) ||
            isset($options['maxwidth'])  ||
            isset($options['minheight']) ||
            isset($options['minwidth'])
        ) {
            $h = $media->getMetadata('height');
            $w = $media->getMetadata('width');

            if  (isset($options['minheight']) && $options['minheight'] > $h) {
                $w = $w * $options['minheight'] / $h;
                $h = $options['minheight'];
            }

            if  (isset($options['minwidth']) && $options['minwidth'] > $w) {
                $h = $h * $options['minwidth'] / $w;
                $w = $options['minwidth'];
            }

            if  (isset($options['maxheight']) && $options['maxheight'] < $h) {
                $w = $w * $options['maxheight'] / $h;
                $h = $options['maxheight'];
            }

            if  (isset($options['maxwidth']) && $options['maxwidth'] < $w) {
                $h = $h * $options['maxwidth'] / $w;
                $w = $options['maxwidth'];
            }

            $image->forceResize($w, $h);
        }

        if (isset($options['grayscale'])) {
            $image->grayscale();
        }

        if (isset($options['scale'])) {
            $w = $media->getMetadata('width') * $options['scale'] / 100;

            $image->scaleResize($w);
        }

        $image->save($imageCachePath, $options['format'], 95);

        $responseMedia
            ->setContent(file_get_contents($imageCachePath))
            ->setContentType(finfo_file($finfo, $imageCachePath))
            ->setContentLength(filesize($imageCachePath))
            ->setLastModifiedAt(new \DateTime('now'))
        ;

        return $responseMedia;
    }
}
