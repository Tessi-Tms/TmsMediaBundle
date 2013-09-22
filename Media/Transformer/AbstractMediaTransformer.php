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

abstract class AbstractMediaTransformer implements MediaTransformerInterface
{
    protected $cacheManager;

    /**
     * Constructor
     *
     * @param DoctrineCach|null $cacheManager
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
        return in_array($parameters, $this->getAvailableParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Filesystem $storageProvider, Media $media, $format, $parameters = array())
    {
        // Cache if configure
        return $this->process($storageProvider, $media, $format, $parameters);
    }
}
