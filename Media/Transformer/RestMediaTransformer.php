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
use IDCI\Bundle\ExporterBundle\Service\Manager as Exporter;

class RestMediaTransformer extends AbstractMediaTransformer
{
    protected $exporter;

    /**
     * Constructor
     */
    public function __construct($cacheManager = null, Exporter $exporter)
    {
        parent::__construct($cacheManager);
        $this->exporter = $exporter;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array('json', 'xml', 'csv');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableParameters()
    {
        return array(null);
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, $format, $parameters = array())
    {
        $responseMedia = new ResponseMedia();
        $export = $this->exporter->export(array($media), $format);

        $responseMedia->setContent($export->getContent());
        $responseMedia->setContentType(
            sprintf('%s; charset=UTF-8', $export->getContentType())
        );
        $responseMedia->setETag(sprintf('%s-%s',
            $media->getReference(),
            $format
        ));
        $responseMedia->setLastModifiedAt(new \DateTime('now'));

        return $responseMedia;
    }
}
