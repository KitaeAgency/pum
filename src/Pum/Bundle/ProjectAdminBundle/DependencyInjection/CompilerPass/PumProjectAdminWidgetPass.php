<?php

namespace Pum\Bundle\ProjectAdminBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PumProjectAdminWidgetPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
    	if (!$container->hasDefinition('pum.project.admin.widgets')) {
            return;
        }

        $definition = $container->getDefinition('pum.project.admin.widgets');
        $taggedServices = $container->findTaggedServiceIds('pum.project.admin.widget');

        foreach ($taggedServices as $id => $attributes) {
        	$definition->addMethodCall('addWidget', array(new Reference($id)));
        }
    }
}