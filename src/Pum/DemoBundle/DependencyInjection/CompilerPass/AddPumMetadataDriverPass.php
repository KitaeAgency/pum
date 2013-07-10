<?php

namespace Pum\DemoBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddPumMetadataDriverPass implements CompilerPassInterface
{
    const METADATA_ID = 'doctrine.orm.metadata.pum';

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getParameter('doctrine.entity_managers') as $name => $id) {
            $container->getDefinition(sprintf('doctrine.orm.%s_metadata_driver', $name))
                ->addMethodCall('addDriver', array(new Reference(self::METADATA_ID), 'Pum\Object'))
            ;
        }
    }
}
