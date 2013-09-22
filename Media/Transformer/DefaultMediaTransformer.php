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

class DefaultMediaTransformer extends AbstractMediaTransformer
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array(null);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableParameters()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, $format, $parameters = array())
    {
        $responseMedia = new ResponseMedia($media);
        $responseMedia->setContent(
            $storageProvider->read($media->getReference())
        );

        return $responseMedia;
    }
}
