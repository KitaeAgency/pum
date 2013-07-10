<?php

namespace Pum\DemoBundle;

use Pum\DemoBundle\DependencyInjection\CompilerPass\AddPumMetadataDriverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PumDemoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddPumMetadataDriverPass());
    }
}
