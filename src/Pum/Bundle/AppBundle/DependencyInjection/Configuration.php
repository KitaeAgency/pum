<?php

namespace Pum\Bundle\AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('pum_app')
            ->children()
                ->arrayNode('mailer')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('from')->defaultValue('no-reply@kitae.fr')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
