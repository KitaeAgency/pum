<?php

namespace Pum\Bundle\CoreBundle;

use Pum\Bundle\CoreBundle\DependencyInjection\Compiler\AddValidationLoaderPass;
use Pum\Bundle\CoreBundle\DependencyInjection\Compiler\PumSubscriberPass;
use Pum\Bundle\CoreBundle\DependencyInjection\Compiler\BuilderRegistryPass;
use Pum\Bundle\CoreBundle\DependencyInjection\Compiler\PumLoaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PumCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BuilderRegistryPass());
        $container->addCompilerPass(new PumSubscriberPass());
        $container->addCompilerPass(new AddValidationLoaderPass());
        $container->addCompilerPass(new PumLoaderPass());
    }
}
