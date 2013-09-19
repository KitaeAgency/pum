<?php

namespace Pum\Bundle\CoreBundle;

use Pum\Bundle\CoreBundle\DependencyInjection\Compiler\AddValidationLoaderPass;
use Pum\Bundle\CoreBundle\DependencyInjection\Compiler\SchemaManagerExtensionPass;
use Pum\Bundle\CoreBundle\DependencyInjection\Compiler\BuilderRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PumCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BuilderRegistryPass());
        $container->addCompilerPass(new SchemaManagerExtensionPass());
        $container->addCompilerPass(new AddValidationLoaderPass());
    }
}
