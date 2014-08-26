<?php

namespace Pum\Bundle\ProjectAdminBundle;

use Pum\Bundle\ProjectAdminBundle\DependencyInjection\CompilerPass\PumCustomViewRepositoriesPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PumProjectAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PumCustomViewRepositoriesPass());
    }
}
