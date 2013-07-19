<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds tagged "pum.extension" to the schema manager config.
 */
class SchemaManagerExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('pum_core.schema_manager')) {
            return;
        }

        $definition = $container->getDefinition('pum_core.schema_manager');

        $services = array();
        foreach ($container->findTaggedServiceIds('pum.extension') as $id => $attributes) {
            $definition->addMethodCall('addExtension', array(new Reference($id)));
        }
    }
}
