<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddValidationLoaderPass implements CompilerPassInterface
{
    const LOADER_CHAIN_ID = 'validator.mapping.loader.loader_chain';
    const LOADER_ID       = 'validator.loader.pum';

    const VALIDATOR_BUILDER_CLASS = 'validator.builder';
    const PUM_VALIDATOR_CLASS     = 'Pum\Bundle\CoreBundle\Validator\Validation';
    const PUM_OBJECT_FACTORY      = 'pum';

    public function process(ContainerBuilder $container)
    {
        /* [TODO Alex] Not working anymore since symfony 2.5 with validator builder */
        if (true === $container->hasDefinition(self::LOADER_CHAIN_ID)) {
            $definition   = $container->getDefinition(self::LOADER_CHAIN_ID);
            $collection   = $definition->getArgument(0);
            $collection[] = new Reference(self::LOADER_ID);
            $definition->replaceArgument(0, $collection);
        }
        // To be removed when the validator loader is back
        elseif (true === $container->hasDefinition(self::VALIDATOR_BUILDER_CLASS)) {
            $definition = $container->getDefinition(self::VALIDATOR_BUILDER_CLASS);
            $definition
                ->setFactoryClass(self::PUM_VALIDATOR_CLASS)
                ->addMethodCall('setFactory', array(new Reference(self::PUM_OBJECT_FACTORY)))
            ;
        }
    }
}
