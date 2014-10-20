<?php

namespace Pum\Bundle\WoodworkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('pum_woodwork')
            ->children()
                ->booleanNode('relation_in_beam')->defaultFalse()->end()
            ->end()
        ;

        return $builder;
    }
}
