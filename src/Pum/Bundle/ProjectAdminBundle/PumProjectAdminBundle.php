<?php

namespace Pum\Bundle\ProjectAdminBundle;

use Pum\Bundle\ProjectAdminBundle\DependencyInjection\CompilerPass\PumCustomViewRepositoriesPass;
use Pum\Bundle\ProjectAdminBundle\DependencyInjection\CompilerPass\PumProjectAdminWidgetPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PumProjectAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PumCustomViewRepositoriesPass());
        $container->addCompilerPass(new PumProjectAdminWidgetPass());
    }
}
