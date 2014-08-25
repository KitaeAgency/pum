<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddValidationLoaderPass implements CompilerPassInterface
{
    const LOADER_CHAIN_ID = 'validator.mapping.loader.loader_chain';
    const LOADER_ID       = 'validator.loader.pum';

    public function process(ContainerBuilder $container)
    {
        /* [TODO Alex] Not working anymore since symfony 2.5 with validator builder */
        if (false === $container->hasDefinition(self::LOADER_CHAIN_ID)) {
            return;
        }

        $definition   = $container->getDefinition(self::LOADER_CHAIN_ID);
        $collection   = $definition->getArgument(0);
        $collection[] = new Reference(self::LOADER_ID);
        $definition->replaceArgument(0, $collection);
    }
}
