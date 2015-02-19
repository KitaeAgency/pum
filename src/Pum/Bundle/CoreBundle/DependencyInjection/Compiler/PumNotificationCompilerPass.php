<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Pum\Bundle\CoreBundle\Entity\NotificationRepository;

class PumNotificationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('pum.notification');
        $definition->addMethodCall('setDefaultOptions', array($container->getParameter('pum_core.notification')));

        $container->register('pum.notification_repository', 'Pum\Bundle\CorepBundle\Entity\NotificationRepository')
            ->setFactoryService('doctrine')
            ->setFactoryMethod('getRepository')
            ->addArgument(NotificationRepository::NOTIFICATION_CLASS)
        ;
    }
}
