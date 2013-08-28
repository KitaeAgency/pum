<?php

namespace Pum\Bundle\TypeExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class PumTypeExtraExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if(!$container->hasParameter('pum_type_extra.media.enabled')) {
            $container->setParameter('pum_type_extra.media.enabled', $config['media']['enabled']);
        }

        if(!$container->hasParameter('pum_type_extra.media.storage.filesystem.directory')) {
            $container->setParameter('pum_type_extra.media.storage.filesystem.directory', $config['media']['storage']['filesystem']['directory']);
        }

        if(!$container->hasParameter('pum_type_extra.media.storage.filesystem.path')) {
            $container->setParameter('pum_type_extra.media.storage.filesystem.path', $config['media']['storage']['filesystem']['path']);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('types.xml');
        $loader->load('services.xml');
    }
}
