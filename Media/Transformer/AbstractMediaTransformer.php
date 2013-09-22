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
use Tms\Bundle\MediaBundle\Exception\UndefinedMediaTransformerParameterException;

abstract class AbstractMediaTransformer implements MediaTransformerInterface
{
    protected $cacheManager;

    /**
     * Constructor
     *
     * @param DoctrineCache|null $cacheManager
     */
    public function __construct($cacheManager = null)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Get available formats
     *
     * @return array
     */
    abstract protected function getAvailableFormats();

    /**
     * Get available parameters
     *
     * @return array
     */
    abstract protected function getAvailableParameters();

    /**
     * process the transformation
     *
     * @param Filesystem $storageProvider
     * @param Media $media
     * @param string $format
     * @param array $parameters
     * @return ResponseMedia
     */
    abstract protected function process(Filesystem $storageProvider, Media $media, $format, $parameters = array());

    /**
     * {@inheritdoc}
     */
    public function checkFormat($format)
    {
        return in_array($format, $this->getAvailableFormats());
    }

    /**
     * {@inheritdoc}
     */
    public function checkParameters($parameters)
    {
        $diff = array_diff(
            array_keys($parameters),
            $this->getAvailableParameters()
        );

        if (count($diff) == 0) {
            return true;
        }

        throw new UndefinedMediaTransformerParameterException($this, array_values($diff));
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Filesystem $storageProvider, Media $media, $format, $parameters = array())
    {
        $this->checkParameters($parameters);

        // Cache if configure
        return $this->process($storageProvider, $media, $format, $parameters);
    }
}
