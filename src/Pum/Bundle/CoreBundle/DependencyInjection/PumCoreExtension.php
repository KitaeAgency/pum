<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class PumCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ($config['view']['enabled']) {
            $loader->load('view.xml');
            $container->setParameter('pum_core.view.resources', $config['view']['resources']);
        }

        if ($config['em_factory']) {
            $loader->load('em_factory.xml');
        }

        $loader->load('pum.xml');
        $loader->load('form.xml');
        $loader->load('twig.xml');
        $loader->load('validator.xml');
    }
}
