<?php

/**
 *
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Julien ANDRE <julien.andre1907@gmail.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Media\Transformer;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tms\Bundle\MediaBundle\Entity\Media;
use Tms\Bundle\MediaBundle\Media\ResponseMedia;
use Tms\Bundle\MediaBundle\Media\ImageMedia;
use Tms\Bundle\MediaBundle\Exception\ImagickException;
use Gaufrette\Filesystem;
use IDCI\Bundle\ExporterBundle\Service\Manager as Exporter;

class BinaryMediaTransformer extends ImageMediaTransformer
{

    /**
     * Constructor
     *
     * @param $Exporter;
     */
    public function __construct($cacheDirectory, Exporter $exporter)
    {
        parent::__construct($cacheDirectory);
        $this->exporter = $exporter;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array('bin');
    }

    /**
    * {@inheritdoc}
    */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setOptional(array(
            'mediaFormat',
            'outputFormat'
        ));
    }

     /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, array $options = array())
    {
    //TODO securize Format parameters, present and valid+

        $this->processParameters = array(
            'storageProvider' => $storageProvider,
            'media'           => $media,
            'options'         => $options
        );
        $options['format'] = $options['mediaFormat'];
        parent::process($storageProvider, $media, $options);
        
    }

     /**
     * Create a response media
     *
     * @param string $content
     * @param string $mimeType
     * @param integer $size
     * @param \DateTime $date
     * @return ResponseMedia
     */
    protected function createResponseMedia($content, $mimeType, $size, $date)
    {
        $responseMedia = new ResponseMedia();
        $this->processParameters['media']->setRaw($content);        
        $export = $this->exporter->export($this->processParameters['media'], $this->processParameters['options']['outputFormat']);

        $responseMedia
            ->setContent($export->getContent())
            ->setContentType(sprintf(
                '%s; charset=UTF-8',
                $export->getContentType()
            ))
            ->setLastModifiedAt($this->processParameters['media']->getCreatedAt())
            ->setContentLength($size)
        ;

        return $responseMedia;
    }
}
