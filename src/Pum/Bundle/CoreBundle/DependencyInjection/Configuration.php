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
                ->arrayNode('doctrine')
                    ->useAttributeAsKey('name')
                    ->treatNullLike(array())
                    ->prototype('scalar')->end()
                    ->defaultValue(array('_pum' => array('dql' => array())))
                    ->prototype('array')
                        ->beforeNormalization()
                        ->ifTrue(function($v) {
                            return !isset($v['dql']);
                        })
                        ->then(function($v) {
                            return array('dql' => array());
                        })
                        ->end()
                        ->children()
                            ->arrayNode('dql')
                                ->fixXmlConfig('string_function')
                                ->fixXmlConfig('numeric_function')
                                ->fixXmlConfig('datetime_function')
                                ->children()
                                    ->arrayNode('string_functions')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('numeric_functions')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('datetime_functions')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
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
                ->arrayNode('notification')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('from')->defaultValue('notification@kitea.fr')->end()
                        ->arrayNode('content')
                            ->children()
                                ->scalarNode('title')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('assetic_bundles')->prototype('scalar')->end()->end()
                ->arrayNode('elasticsearch')
                    ->children()
                        ->arrayNode('hosts')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
        ;

        return $builder;
    }
}
