<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class GaufretteCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tms_media.manager'))
            return;

        $definition = $container->getDefinition('tms_media.manager');

        $providersConfig = $container->getParameter('tms_media.config.providers');

        foreach ($providersConfig as $providerId => $providerConfig)
        {
            $providerDefinition = new DefinitionDecorator('tms_media.storage_provider');
            $providerDefinition->setAbstract(false);
            $providerDefinition->replaceArgument(0, new Reference($providerConfig['service']));
            $providerServiceId = sprintf(
                'tms_media.storage_provider.%s',
                $providerId
            );
            $container->setDefinition($providerServiceId, $providerDefinition);

            $definition->addMethodCall(
                'addStorageProvider',
                array(new Reference($providerServiceId))
            );
        }
    }
}
