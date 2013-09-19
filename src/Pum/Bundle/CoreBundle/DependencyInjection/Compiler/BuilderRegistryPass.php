<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class BuilderRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('pum_core.builder_registry')) {
            return;
        }

        $definition = $container->getDefinition('pum_core.builder_registry');

        $typeIds = array();
        foreach ($container->findTaggedServiceIds('pum.type') as $id => $attributes) {
            $alias = isset($attributes[0]['alias']) ? $attributes[0]['alias'] : 0;
            if (isset($typeIds[$alias])) {
                throw new \RuntimeException(sprintf('Two services in container are tagged as type "%s": "%s" and "%s".', $alias, $typeIds[$alias], $id));
            }

            $typeIds[$alias] = $id;
        }

        $typeExtensionIds = array();
        foreach ($container->findTaggedServiceIds('pum.type_extension') as $id => $attributes) {
            $alias = isset($attributes[0]['alias']) ? $attributes[0]['alias'] : 0;
            if (isset($typeExtensionIds[$alias])) {
                $typeExtensionIds[$alias][] = $id;
            } else {
                $typeExtensionIds[$alias] = array($id);
            }

            $typeIds[$alias] = $id;
        }

        $behaviorIds = array();
        foreach ($container->findTaggedServiceIds('pum.behavior') as $id => $attributes) {
            $alias = isset($attributes[0]['alias']) ? $attributes[0]['alias'] : 0;
            if (isset($behaviorIds[$alias])) {
                throw new \RuntimeException(sprintf('Two services in container are taggued as behavior "%s": "%s" and "%s".', $alias, $behaviorIds[$alias], $id));
            }

            $behaviorIds[$alias] = $id;
        }

        $container->setParameter('pum_core.builder_registry.type_ids', $typeIds);
        $container->setParameter('pum_core.builder_registry.type_extension_ids', $typeExtensionIds);
        $container->setParameter('pum_core.builder_registry.behavior_ids', $behaviorIds);
    }
}
