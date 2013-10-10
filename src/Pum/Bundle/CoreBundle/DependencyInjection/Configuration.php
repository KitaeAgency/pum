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
                ->booleanNode('em_factory')->defaultFalse()->end()
                ->arrayNode('view')
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->always(function ($vals) {
                            if (is_array($vals) && !isset($vals['resources'])) {
                                return array(
                                    'filesystem_loader' => false,
                                    'enabled' => true,
                                    'resources' => $vals
                                );
                            }

                            return $vals;
                        })
                    ->end()
                ->children()
                    ->booleanNode('filesystem_loader')->defaultFalse()->end()
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
