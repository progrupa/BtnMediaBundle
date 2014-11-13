<?php

namespace Btn\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('btn_media');

        $rootNode
            ->children()
                ->arrayNode('media')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->cannotBeEmpty()->defaultValue('Btn\\MediaBundle\\Entity\\Media')->end()
                        ->arrayNode('allowed_extensions')
                            ->defaultValue(array('jpeg', 'jpg', 'png', 'zip', 'pdf'))
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->scalarNode('max_size')->defaultValue(null)->example('10M')->end()
                    ->end()
                ->end()
                ->arrayNode('media_category')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->cannotBeEmpty()
                            ->defaultValue('Btn\\MediaBundle\\Entity\\MediaCategory')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
