<?php

namespace Pum\Bundle\WoodworkBundle\DependencyInjection\CompilerPass;

use Pum\Bundle\WoodworkBundle\Entity\UserRepository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PumUserRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$this->isPumUserEnabled($container)) {
            return;
        }

        $container->register('pum.user_repository', 'Pum\Bundle\WoodworkBundle\Entity\UserRepository')
            ->setFactoryService('doctrine')
            ->setFactoryMethod('getRepository')
            ->addArgument(UserRepository::USER_CLASS)
        ;
    }

    /**
     * Method to check if the container is using the PUM security layer.
     */
    private function isPumUserEnabled(ContainerBuilder $container)
    {
        if (!$container->has('security.user.provider.concrete.doctrine')) {
            return false;
        }

        $def = $container->getDefinition('security.user.provider.concrete.doctrine');

        $arg = $def->getArgument(0);

        if ($arg === UserRepository::USER_CLASS || is_subclass_of($arg, UserRepository::USER_CLASS)) {
            return true;
        }

        return false;
    }

}
