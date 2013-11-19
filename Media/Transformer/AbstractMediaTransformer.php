<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Media\Transformer;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tms\Bundle\MediaBundle\Entity\Media;
use Gaufrette\Filesystem;
use Tms\Bundle\MediaBundle\Media\ResponseMedia;

abstract class AbstractMediaTransformer implements MediaTransformerInterface
{
    /**
     * Get available formats
     *
     * @return array
     */
    abstract protected function getAvailableFormats();

    /**
     * process the transformation
     *
     * @param Filesystem $storageProvider
     * @param Media $media
     * @param array $options
     * @return ResponseMedia
     */
    abstract protected function process(Filesystem $storageProvider, Media $media, $options = array());

    /**
     * {@inheritdoc}
     */
    public function checkFormat($format)
    {
        return in_array($format, $this->getAvailableFormats());
    }

    /**
     * Set default options
     *
     * @param OptionsResolverInterface
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'format'
        ));
        $resolver->setDefaults(array(
            'format' => $this->getAvailableFormats()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Filesystem $storageProvider, Media $media, $options = array())
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $resolvedOptions = $resolver->resolve($options);

        $responseMedia = $this
            ->process($storageProvider, $media, $resolvedOptions)
            ->setETag(sprintf('%s%s',
                $media->getReference(),
                null !== $options['format'] ? '.'.$options['format'] : ''
            ))
        ;

        return $responseMedia;
    }
}
