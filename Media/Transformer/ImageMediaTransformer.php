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
use Tms\Bundle\MediaBundle\Media\ResponseMedia;
use Tms\Bundle\MediaBundle\Handler\ImageHandler;
use Gaufrette\Filesystem;

class ImageMediaTransformer extends AbstractMediaTransformer
{
    protected $imageHandler;
    private $cacheDirectory;
    private $fileinfo;

    /**
     * @param ImageHandler $imageHandler
     * @param string $cacheDirectory
     */
    public function __construct(ImageHandler $imageHandler, $cacheDirectory)
    {
        $this->imageHandler = $imageHandler;
        $this->cacheDirectory = $cacheDirectory;
        $this->fileinfo = finfo_open(FILEINFO_MIME_TYPE);
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
            'minheight',
            'rotate'
        ));

        $resolver->setDefaults(array(
            'width' => null,
            'height' => null,
            'scale' => null,
            'grayscale' => null,
            'maxwidth' => null,
            'maxheight' => null,
            'minwidth' => null,
            'minheight' => null,
            'rotate' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media)
    {
        $originalContent = $storageProvider->read($media->getReference());

        if ($this->getFormat() === $media->getExtension() && false === $this->isTransformationNeeded()) {
            return $this->createResponseMedia(
                $originalContent,
                $media->getMimeType(),
                $media->getSize(),
                $media->getCreatedAt()
            );
        }

        $cachedImageSourcePath = $this->getCachedImageSourcePath($media->getReference());
        $cachedImage = $this->getCachedImage($cachedImageSourcePath);
        if (null !== $cachedImage) {
            return $cachedImage;
        }

        $cachedImage = $this->createCachedImage($originalContent, $media->getReference(), $media->getExtension());
        $image = $this->imageHandler->read($cachedImage);

        if ($this->getRotate()) {
            $image->rotate($this->getRotate());
        }

        if ($this->getGrayscale()) {
            $image->grayscale();
        }

        if ($this->getWidth() || $this->getHeight()) {
            $image->resize($this->getWidth(), $this->getHeight());
        }

        if ($this->getMaxheight() ||
            $this->getMaxwidth()  ||
            $this->getMinheight() ||
            $this->getMinwidth()
        ) {
            $height = $media->getMetadata('height');
            $width = $media->getMetadata('width');

            if ($this->getMinheight() && $this->getMinheight() > $height) {
                $width = $width * $this->getMinheight() / $height;
                $height = $this->getMinheight();
            }

            if ($this->getMinwidth() && $this->getMinwidth() > $width) {
                $height = $height * $this->getMinwidth() / $width;
                $width = $this->getMinwidth();
            }

            if ($this->getMaxheight() && $this->getMaxheight() < $height) {
                $width = $width * $this->getMaxheight() / $height;
                $height = $this->getMaxheight();
            }

            if ($this->getMaxwidth() && $this->getMaxwidth() < $width) {
                $height = $height * $this->getMaxwidth() / $width;
                $width = $this->getMaxwidth();
            }

            $image->resize($width, $height);
        }

        if ($this->getScale()) {
            $width = $media->getMetadata('width') * $this->getScale() / 100;
            $image->resize($width, null);
        }

        $image
            //->format($this->getFormat())
            ->quality(95)
            ->save($cachedImageSourcePath);

        return $this->createResponseMedia(
                file_get_contents($cachedImageSourcePath),
                finfo_file($this->fileinfo, $cachedImageSourcePath),
                filesize($cachedImageSourcePath),
                new \DateTime('now')
        );
    }

    private function getWidth()
    {
        return $this->options['width'];
    }

    private function getHeight()
    {
        return $this->options['height'];
    }

    private function getScale()
    {
        return $this->options['scale'];
    }

    private function getGrayscale()
    {
        return $this->options['grayscale'];
    }

    private function getMaxwidth()
    {
        return $this->options['maxwidth'];
    }

    private function getMaxheight()
    {
        return $this->options['maxheight'];
    }

    private function getMinwidth()
    {
        return $this->options['minwidth'];
    }

    private function getMinheight()
    {
        return $this->options['minheight'];
    }

    private function getRotate()
    {
        return $this->options['rotate'];
    }

    /**
     * @param string $reference
     * @return string
     */
    private function getCachedImageSourcePath($reference)
    {
        $imageCacheName = sprintf('%s_%s.%s',
            $reference,
            sprintf("%u", crc32(serialize($this->options))),
            $this->getFormat()
        );
        $imageCachePath = sprintf('%s%s', $this->cacheDirectory, $imageCacheName);

        return $imageCachePath;
    }

    /**
     *
     * @param string $sourcePath
     */
    private function getCachedImage($sourcePath)
    {
        if (!file_exists($sourcePath)) {
            return null;
        }

        $date = new \DateTime();
        $date->setTimestamp(filemtime($sourcePath));

        return $this->createResponseMedia(
                file_get_contents($sourcePath),
                finfo_file($this->fileinfo, $sourcePath),
                filesize($sourcePath),
                $date
        );
    }

    /**
     *
     * @param Object $originalContent
     * @param string $reference
     * @param string $extension
     * @return Object
     */
    private function createCachedImage($originalContent, $reference, $extension)
    {
        $image = sprintf('%s%s.%s',
                $this->cacheDirectory,
                $reference,
                $extension
        );
        file_put_contents($image, $originalContent);

        return $image;
    }

    /**
     * @param Object $content
     * @param string $mimeType
     * @param string $size
     * @param \DateTime $date
     */
    private function createResponseMedia($content, $mimeType, $size, $date)
    {
        $responseMedia = new ResponseMedia();
        $responseMedia
            ->setContent($content)
            ->setContentType($mimeType)
            ->setContentLength($size)
            ->setLastModifiedAt($date)
        ;

        return $responseMedia;
    }

    /**
     * Detects if the image needs to be transformed
     *
     * @return boolean
     */
    private function isTransformationNeeded()
    {
        if ($this->getWidth()     ||
            $this->getHeight()    ||
            $this->getScale()     ||
            $this->getGrayscale() ||
            $this->getMaxwidth()  ||
            $this->getMaxheight() ||
            $this->getMinwidth()  ||
            $this->getMinheight() ||
            $this->getRotate()
        ) {
            return true;
        }

        return false;
    }
}
