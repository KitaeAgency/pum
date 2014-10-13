<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder
            ->root('pum_core')
            ->children()
                ->booleanNode('validation')->defaultTrue()->end()
                ->booleanNode('em_factory')->defaultFalse()->end()
                ->arrayNode('view')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->arrayNode('mode')
                        ->prototype('scalar')->end()
                        ->defaultValue(array('filesystem'))
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
