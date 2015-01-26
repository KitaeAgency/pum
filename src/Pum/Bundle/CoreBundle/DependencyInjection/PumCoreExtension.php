<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;

class PumCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if(!$container->hasParameter('pum_core.validation')) {
            $usePumValidation = (isset($config['validation']) && $config['validation']) ? true : false;
            $container->setParameter('pum_core.validation', $usePumValidation);
        }

        if ($config['view']['enabled']) {
            // we must load this filesystem loader *before* other loaders, or it won't be prior on loading
            if (isset($config['view']['mode'])) {
                if (in_array('filesystem', $config['view']['mode'])) {
                    $this->registerPumViewFolders($container);
                }

                foreach (array_unique($config['view']['mode']) as $mode) {
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
        $loader->load('search.xml');
        $loader->load('tree.xml');
        $loader->load('form.xml');
        $loader->load('twig.xml');
        $loader->load('validator.xml');
        $loader->load('translation.xml');
        $loader->load('templating.xml');
        $container->setParameter('pum_core.assetic_bundles', $config['assetic_bundles']);

        if ($config['doctrine']) {
            $definitionService = $container->getDefinition('pum_core.em_factory');
            $isDevMode         = ('dev' == $container->getParameter('kernel.environment')) ? true : false;
            $proxyDir          = null;
            $cache             = null;

            foreach ($config['doctrine'] as $key => $values) {
                $ormConfigName = sprintf('pum.doctrine.orm.%s_configuration', $key);

                $ormConfigDef = new Definition();

                $ormConfigDef->setClass('Doctrine\ORM\Configuration');
                $ormConfigDef->setFactoryClass('Doctrine\ORM\Tools\Setup');
                $ormConfigDef->setFactoryMethod('createConfiguration');
                $ormConfigDef->setArguments(array($isDevMode, $proxyDir, $cache));

                $ormConfig = $container->setDefinition($ormConfigName, $ormConfigDef);

                if ($config['doctrine'][$key]['dql']) {
                    foreach ($config['doctrine'][$key]['dql']['string_functions'] as $name => $function) {
                        $ormConfig->addMethodCall('addCustomStringFunction', array($name, $function));
                    }

                    foreach ($config['doctrine'][$key]['dql']['numeric_functions'] as $name => $function) {
                        $ormConfig->addMethodCall('addCustomNumericFunction', array($name, $function));
                    }

                    foreach ($config['doctrine'][$key]['dql']['datetime_functions'] as $name => $function) {
                        $ormConfig->addMethodCall('addCustomDatetimeFunction', array($name, $function));
                    }
                }
                $ormConfig->addMethodCall('setAutoGenerateProxyClasses', array(true));

                $definitionService->addMethodCall('addConfiguration', array($key, new Reference($ormConfigName)));
            }
        }
    }

    private function registerPumViewFolders(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $folders = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {

            if (is_dir($dir = $container->getParameter('kernel.root_dir').'/Resources/'.$bundle.'/pum_views')) {
                $folders[$bundle] = $dir;
            }

            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/pum_views')) {
                $folders[$bundle] = $dir;
            }
        }

        $container->setParameter('pum_core.view.folders', $folders);
    }
}
