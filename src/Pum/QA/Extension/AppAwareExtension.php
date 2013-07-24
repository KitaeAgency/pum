<?php

namespace Pum\QA\Extension;

use Behat\Behat\Extension\ExtensionInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class AppAwareExtension implements ExtensionInterface
{
    function load(array $config, ContainerBuilder $container)
    {
        $container->setParameter('pum.app_dir', $config['app_dir']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ ));
        $loader->load('appaware.xml');
    }

    function getConfig(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('app_dir')->isRequired()->end()
            ->end()
        ;
    }

    function getCompilerPasses()
    {
        return array();
    }
}
