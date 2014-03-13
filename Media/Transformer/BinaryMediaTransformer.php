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

class BinaryMediaTransformer extends AbstractMediaTransformer
{
    /**
     * The image transformer.
     *
     * @var MediaTransformerInterface
     */
    private $imageTransformer;

    /**
     * The rest transformer.
     *
     * @var MediaTransformerInterface
     */
    private $restTransformer;

    /**
     * Constructor
     *
     * @param $Exporter;
     */
    public function __construct(
        AbstractMediaTransformer $imageTransformer,
        AbstractMediaTransformer $restTransformer
    )
    {
        $this->imageTransformer = $imageTransformer;
        $this->restTransformer = $restTransformer;
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
            'resize',
            'scale',
            'grayscale',
            'rotate',
            'width',
            'height',
            'maxwidth',
            'maxheight',
            'minwidth',
            'minheight',
            'mediaFormat',
            'outputFormat'
        ));

        $resolver->setRequired(array(
            'mediaFormat',
            'outputFormat'
        ));
    }

     /**
     * {@inheritdoc}
     */
    protected function process(Filesystem $storageProvider, Media $media, array $options = array())
    {   
        $options['format'] = $options['mediaFormat'];
        $res = $this->imageTransformer->process($storageProvider, $media, $options);
        $options['format'] = $options['outputFormat'];
        $media->setRaw((string)$res->getContent());
        return $this->restTransformer->process($storageProvider, $media, $options);
    }
}