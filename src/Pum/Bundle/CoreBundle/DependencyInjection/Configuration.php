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
                ->arrayNode('view')
                ->addDefaultsIfNotSet()
                ->beforeNormalization()
                    ->always(function ($vals) {
                        if (is_array($vals) && !isset($vals['resources'])) {
                            return array(
                                'enabled' => true,
                                'resources' => $vals
                            );
                        }

                        return $vals;
                    })
                ->end()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->arrayNode('resources')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
