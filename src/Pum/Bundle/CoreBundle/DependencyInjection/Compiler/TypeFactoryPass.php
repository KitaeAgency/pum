<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged "pum.type" to the type factory.
 */
class TypeFactoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('pum_core.type_factory')) {
            return;
        }

        $definition = $container->getDefinition('pum_core.type_factory');

        $services = array();
        foreach ($container->findTaggedServiceIds('pum.type') as $id => $attributes) {
            $alias = isset($attributes[0]['alias']) ? $attributes[0]['alias'] : 0;
            if (isset($services[$alias])) {
                throw new \RuntimeException(sprintf('Two services request to be "%s" type: "%s" and "%s".', $alias, $services[$alias], $id));
            }

            $services[$alias] = $id;
        }

        $container->setParameter('pum_core.type_factory.service_ids', $services);
    }
}
