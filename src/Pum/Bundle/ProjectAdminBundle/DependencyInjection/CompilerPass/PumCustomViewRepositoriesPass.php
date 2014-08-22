<?php

namespace Pum\Bundle\ProjectAdminBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pum\Bundle\ProjectAdminBundle\Entity\CustomViewRepository;

class PumCustomViewRepositoriesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->register('pum.customview_repository', 'Pum\Bundle\ProjectAdminBundle\Entity\CustomViewRepository')
            ->setFactoryService('doctrine')
            ->setFactoryMethod('getRepository')
            ->addArgument(CustomViewRepository::CUSTOMVIEW_CLASS)
        ;
    }
}
