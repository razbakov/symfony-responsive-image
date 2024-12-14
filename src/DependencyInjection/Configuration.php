<?php

namespace Ommax\ResponsiveImageBundle\DependencyInjection;

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
                ->scalarNode('provider')
                    ->info('Image provider to use')
                    ->isRequired()
                ->end()
                ->scalarNode('missing_image_placeholder')
                    ->info('Path to the image shown when source image is missing')
                    ->isRequired()
                ->end()
                ->arrayNode('defaults')
                    ->isRequired()
                    ->children()
                        ->arrayNode('breakpoints')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('name')
                            ->prototype('integer')
                                ->min(1)
                            ->end()
                        ->end()
                        ->enumNode('format')
                            ->values(['webp', 'jpg', 'png', 'avif'])
                        ->end()
                        ->integerNode('quality')
                            ->min(1)
                            ->max(100)
                        ->end()
                        ->enumNode('loading')
                            ->values(['lazy', 'eager'])
                        ->end()
                        ->enumNode('fetchpriority')
                            ->values(['high', 'low', 'auto'])
                        ->end()
                        ->enumNode('fit')
                            ->values(['cover', 'contain', 'fill', 'inside', 'outside'])
                        ->end()
                        ->enumNode('placeholder')
                            ->values(['none', 'blur', 'dominant'])
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('providers')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->useAttributeAsKey('name')
                        ->variablePrototype()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('presets')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->integerNode('width')
                                ->min(1)
                            ->end()
                            ->integerNode('height')
                                ->min(1)
                            ->end()
                            ->scalarNode('ratio')->end()
                            ->scalarNode('sizes')->end()
                            ->enumNode('fit')
                                ->values(['cover', 'contain', 'fill', 'inside', 'outside'])
                            ->end()
                            ->enumNode('loading')
                                ->values(['lazy', 'eager'])
                            ->end()
                            ->enumNode('fetchpriority')
                                ->values(['high', 'low', 'auto'])
                            ->end()
                            ->enumNode('placeholder')
                                ->values(['none', 'blur', 'dominant'])
                            ->end()
                            ->integerNode('quality')
                                ->min(1)
                                ->max(100)
                            ->end()
                            ->booleanNode('preload')
                                ->defaultFalse()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
