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

class DefineMediaProvidersCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tms_media.manager'))
            return;

        $definition = $container->getDefinition('tms_media.manager');

        $ruleServiceIds = array();
        $ruleServices = $container->findTaggedServiceIds(
            'tms_media.rule'
        );
        foreach ($ruleServices as $id => $attributes) {
            $ruleServiceIds[$attributes[0]['alias']] = $id;
        }

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

            // Injection of the rules in the provider.
            foreach ($providerConfig['rules'] as $ruleAlias => $ruleArguments)
            {
                $ruleDefinition = new DefinitionDecorator($ruleServiceIds[$ruleAlias]);
                $ruleDefinition->setAbstract(false);
                $ruleDefinition->replaceArgument(0, $ruleArguments);

                $ruleServiceId = sprintf(
                    '%s.%s',
                    $ruleServiceIds[$ruleAlias],
                    $providerId
                );

                $container->setDefinition($ruleServiceId, $ruleDefinition);

                $providerDefinition->addMethodCall(
                    'addRule',
                    array(new Reference($ruleServiceId))
                );
            }

            $container->setDefinition($providerServiceId, $providerDefinition);

            $definition->addMethodCall(
                'addStorageProvider',
                array(new Reference($providerServiceId))
            );
        }
    }
}
