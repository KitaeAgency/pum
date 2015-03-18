<?php

namespace Pum\Bundle\WoodworkBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class PumWoodworkExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if(!$container->hasParameter('pum_woodwork.relation_in_beam')) {
            $relationInBeam = (isset($config['relation_in_beam']) && $config['relation_in_beam']) ? true : false;
            $container->setParameter('pum_woodwork.relation_in_beam', $relationInBeam);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('form.xml');
        $loader->load('services.xml');
        $loader->load('permission.xml');
    }
}
