<?php

namespace Pum\Bundle\WoodworkBundle;

use Pum\Bundle\WoodworkBundle\DependencyInjection\CompilerPass\PumSecurityRepositoriesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PumWoodworkBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PumSecurityRepositoriesPass());
    }
}
