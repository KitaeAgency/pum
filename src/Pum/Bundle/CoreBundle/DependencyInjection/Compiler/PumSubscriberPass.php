<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PumSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pum_core.event_dispatcher') && !$container->hasAlias('pum_core.event_dispatcher')) {
            return;
        }

        $definition = $container->getDefinition('pum_core.event_dispatcher');

        foreach ($container->findTaggedServiceIds('pum.subscriber') as $id => $attributes) {
            $definition->addMethodCall('addSubscriber', array(new Reference($id)));
        }
    }
}
