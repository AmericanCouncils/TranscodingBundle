<?php

namespace AC\TranscodingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ac_transcoding');

        $rootNode
            ->children()
                ->arrayNode('handbrake')
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('path')->defaultValue('HandBrakeCLI')->end()
                        ->scalarNode('timeout')->defaultValue(0)->end()
                    ->end()
                ->end()
                ->arrayNode('ffmpeg')
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('path')->defaultValue('ffmpeg')->end()
                        ->scalarNode('timeout')->defaultValue(0)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
