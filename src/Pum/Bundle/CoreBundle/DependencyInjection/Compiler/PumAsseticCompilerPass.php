<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pum\Bundle\CoreBundle\DependencyInjection\PumDirectoryResourceDefinition;

class PumAsseticCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.asset_manager')) {
            return;
        }

        $engines = $container->getParameter('templating.engines');
        $bundles = $container->getParameter('kernel.bundles');
        $asseticBundles = $container->getParameterBag()->resolveValue($container->getParameter('assetic.bundles'));
        $pumAsseticBundles = $container->getParameterBag()->resolveValue($container->getParameter('pum_core.assetic_bundles'));

        foreach ($asseticBundles as $asseticBundle) {
            if (in_array($asseticBundle, $pumAsseticBundles)) {
                $rc = new \ReflectionClass($bundles[$asseticBundle]);
                foreach ($engines as $engine) {
                    $this->setBundlePumDirectoryResources($container, $engine, dirname($rc->getFileName()), $asseticBundle);
                }
            }
        }
    }

    /**
     * Adding new Assetic ressources targets by adding tagged definitions.
     * @param ContainerBuilder $container     The ContainerBuilder
     * @param string           $engine        The templating engine
     * @param string           $bundleDirName The directory path
     * @param string           $asseticBundle The bundle name
     */
    public function setBundlePumDirectoryResources(ContainerBuilder $container, $engine, $bundleDirName, $asseticBundle)
    {
        $container->setDefinition(
            'assetic.'.$engine.'_directory_resource.'.$asseticBundle,
            new PumDirectoryResourceDefinition($asseticBundle, $engine, array(
                $container->getParameter('kernel.root_dir').'/Resources/'.$asseticBundle.'/views',
                $bundleDirName.'/Resources/pum_views',
                $bundleDirName.'/Resources/views',
            ))
        );
    }
}