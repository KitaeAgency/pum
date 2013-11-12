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
            // we must load this filesystem loader *before* other loaders, or it won't be prior on loading
            if (isset($config['view']['mode'])) {
                if (in_array('filesystem', $config['view']['mode'])) {
                    $this->registerPumViewFolders($container);
                }

                foreach ($config['view']['mode'] as $mode) {
                    $loader->load('view_'.$mode.'.xml');
                }
            }

            $loader->load('view.xml');
        }

        if ($config['em_factory']) {
            $loader->load('em_factory.xml');
        }

        $loader->load('pum.xml');
        $loader->load('routing.xml');
        $loader->load('security.xml');
        $loader->load('form.xml');
        $loader->load('twig.xml');
        $loader->load('validator.xml');
        $loader->load('translation.xml');
    }

    private function registerPumViewFolders(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $folders = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {

            if (is_dir($dir = $container->getParameter('kernel.root_dir').'/Resources/'.$bundle.'/pum_views')) {
                $folders[] = $dir;
            }

            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/pum_views')) {
                $folders[] = $dir;
            }
        }

        $container->setParameter('pum_core.view.folders', $folders);
    }
}
