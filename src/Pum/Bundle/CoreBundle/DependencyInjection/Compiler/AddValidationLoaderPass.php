<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddValidationLoaderPass implements CompilerPassInterface
{
    const VALIDATOR_BUILDER_CLASS = 'validator.builder';
    const PUM_OBJECT_FACTORY      = 'pum';

    public function process(ContainerBuilder $container)
    {
        if ($container->getParameter('pum_core.validation') &&  true === $container->hasDefinition(self::VALIDATOR_BUILDER_CLASS)) {
            $definition = $container->getDefinition(self::VALIDATOR_BUILDER_CLASS);
            $definition
                ->addMethodCall('setFactory', array(new Reference(self::PUM_OBJECT_FACTORY)))
            ;
            return;
        }
    }
}
