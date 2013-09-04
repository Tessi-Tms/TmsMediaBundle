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
        if (!$container->hasDefinition('tms_media.manager')) {
            return;
        }

        $definition = $container->getDefinition('tms_media.manager');

        $ruleServiceIds = array();
        $ruleServices = $container->findTaggedServiceIds('tms_media.mapper.rule');

        foreach ($ruleServices as $id => $attributes) {
            $ruleServiceIds[$attributes[0]['alias']] = $id;
        }

        $storageMappersConfig = $container->getParameter('tms_media.config.storage_mappers');
        foreach ($storageMappersConfig as $storageMapperId => $storageMapperConfig) {
            $storageMappersDefinition = new DefinitionDecorator('tms_media.storage_mapper');
            $storageMappersDefinition->setAbstract(false);
            $storageMappersDefinition->replaceArgument(0, new Reference($storageMapperConfig['storage_provider']));
            $storageMappersDefinition->replaceArgument(1, $storageMapperConfig['storage_provider']);
            $storageMapperServiceId = sprintf(
                'tms_media.storage_mapper.%s',
                $storageMapperId
            );

            // Injection of the rules in the provider.
            foreach ($storageMapperConfig['rules'] as $ruleAlias => $ruleArguments) {
                $ruleDefinition = new DefinitionDecorator($ruleServiceIds[$ruleAlias]);
                $ruleDefinition->setAbstract(false);
                $ruleDefinition->replaceArgument(0, $ruleArguments);

                $ruleServiceId = sprintf(
                    '%s.%s',
                    $ruleServiceIds[$ruleAlias],
                    $storageMapperId
                );

                $container->setDefinition($ruleServiceId, $ruleDefinition);

                $storageMappersDefinition->addMethodCall(
                    'addRule',
                    array(new Reference($ruleServiceId))
                );
            }

            $container->setDefinition($storageMapperServiceId, $storageMappersDefinition);

            $definition->addMethodCall(
                'addStorageMapper',
                array(new Reference($storageMapperServiceId))
            );
        }
    }
}
