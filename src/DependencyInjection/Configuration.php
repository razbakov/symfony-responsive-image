<?php

namespace YourVendor\ResponsiveImageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('responsive_image');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('defaults')
                    ->children()
                        ->arrayNode('breakpoints')
                            ->prototype('integer')->end()
                            ->defaultValue([
                                'xs' => 320,
                                'sm' => 640,
                                'md' => 768,
                                'lg' => 1024,
                                'xl' => 1280,
                                '2xl' => 1536
                            ])
                        ->end()
                        ->scalarNode('format')
                            ->defaultValue('webp')
                        ->end()
                        ->integerNode('quality')
                            ->defaultValue(80)
                        ->end()
                        ->booleanNode('lazy')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('priority')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('preload')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('async')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('attributes')
                            ->prototype('variable')->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('presets')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('width')->end()
                            ->scalarNode('height')->end()
                            ->scalarNode('ratio')->end()
                            ->scalarNode('fit')->end()
                            ->scalarNode('focal')->end()
                            ->scalarNode('format')->end()
                            ->integerNode('quality')->end()
                            ->booleanNode('lazy')->end()
                            ->booleanNode('priority')->end()
                            ->booleanNode('preload')->end()
                            ->scalarNode('placeholder')->end()
                            ->scalarNode('placeholder-color')->end()
                            ->scalarNode('background')->end()
                            ->arrayNode('sizes')
                                ->useAttributeAsKey('breakpoint')
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('cache_dir')
                    ->defaultValue('%kernel.project_dir%/public/media/cache')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
} 