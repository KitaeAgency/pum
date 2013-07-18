<?php

namespace Pum\Bundle\CoreBundle;

use Pum\Bundle\CoreBundle\DependencyInjection\Compiler\TypeFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PumCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TypeFactoryPass());
    }
}
