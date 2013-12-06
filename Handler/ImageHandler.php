<?php

namespace Tms\Bundle\MediaBundle\handler;

class ImageHandler
{
    private $imagick;

    public function __construct()
    {
        $this->imagick = new \Imagick();
    }

    /**
     *
     * @param string $imageSourcePath
     * @throws Exception
     * @return \Tms\Bundle\MediaBundle\handler\ImageHandler
     */
    public function read($imageSourcePath)
    {
        if (!$this->imagick->readImage($imageSourcePath)) {
            throw new Exception('Error - ReadImage');
        }

        return $this;
    }

    /**
     *
     * @param string $imageSourcePath
     * @throws Exception
     * @return \Tms\Bundle\MediaBundle\handler\ImageHandler
     */
    public function save($imageSourcePath)
    {
        if (!$this->imagick->writeImage($imageSourcePath)) {
            throw new Exception('Error - WriteImage');
        }
        $this->imagick->destroy();

        return $this;
    }

    /**
     *
     * @param string $name
     * @throws Exception
     * @return \Tms\Bundle\MediaBundle\handler\ImageHandler
     */
    public function name($name)
    {
        if (!$this->imagick->setImageFilename($name)) {
            throw new Exception('Error - ImageFilename');
        }

        return $this;
    }

    public function grayscale()
    {
        if (!$this->imagick->setImageColorspace(2)) {
            throw new Exception('Error - ImageColorspace');
        }

        return $this;
    }

    /**
     *
     * @param integer $width
     * @param integer $height
     * @throws Exception
     * @return \Tms\Bundle\MediaBundle\handler\ImageHandler
     */
    public function resize($width, $height)
    {
        if (!$this->imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1)) {
            throw new Exception('Error - ResizeImage');
        }

        return $this;
    }

    /**
     *
     * @param integer $degrees
     * @throws Exception
     * @return \Tms\Bundle\MediaBundle\handler\ImageHandler
     */
    public function rotate($degrees)
    {
        if (!$this->imagick->rotateImage(new \ImagickPixel(), $degrees)) {
            throw new Exception('Error - RotateImage');
        }

        return $this;
    }

    /**
     *
     * @param integer $quality
     * @throws Exception
     * @return \Tms\Bundle\MediaBundle\handler\ImageHandler
     */
    public function quality($quality)
    {
        if (!$this->imagick->setImageCompressionQuality($quality)) {
            throw new Exception('Error - ImageCompressionQuality');
        }

        return $this;
    }

    /**
     *
     * @param unknown $format
     */
    public function format($format)
    {
        if (!$this->imagick->setImageFormat($format)) {
            throw new Exception('Error - ImageFormat');
        }

        return $this;
    }
}