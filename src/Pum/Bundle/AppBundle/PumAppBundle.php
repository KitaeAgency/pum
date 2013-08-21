<?php

namespace Pum\Bundle\AppBundle;

use Pum\Bundle\AppBundle\DependencyInjection\CompilerPass\PumSecurityRepositoriesPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PumAppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PumSecurityRepositoriesPass());
    }
}
