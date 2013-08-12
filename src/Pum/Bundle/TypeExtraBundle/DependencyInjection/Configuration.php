<?php

namespace Pum\Bundle\TypeExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('pum_type_extra')
            ->children()
                ->arrayNode('media')
                    ->children()
                        ->scalarNode('enabled')->defaultFalse()->end()
                        ->arrayNode('storage')
                            ->children()
                                ->arrayNode('filesystem')
                                    ->children()
                                        ->scalarNode('directory')->defaultValue('%kernel.root_dir%/web/medias')->end()
                                        ->scalarNode('path')->defaultValue('/medias/')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
