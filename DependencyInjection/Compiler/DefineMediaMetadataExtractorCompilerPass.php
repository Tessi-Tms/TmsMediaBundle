<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DefineMediaMetadataExtractorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tms_media.manager')) {
            return;
        }

        $definition = $container->getDefinition('tms_media.manager');

        // MetadataExtractor
        $taggedServices = $container->findTaggedServiceIds('tms_media.metadata_extractor');

        foreach ($taggedServices as $id => $tagAttributes) {
            $definition->addMethodCall(
                'addMetadataExtractor',
                array(new Reference($id), $id)
            );
        }
    }
}
