<?php

namespace GetRepo\BandcampDownloaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bandcamp_downloader');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('save_path')
                    ->cannotBeEmpty()
                    ->defaultValue('%kernel.root_dir%/..')
                ->end()
                ->arrayNode('selectors')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('artist')
                            ->cannotBeEmpty()
                            ->defaultValue('span[itemprop=byArtist]')
                        ->end()
                        ->scalarNode('album')
                            ->cannotBeEmpty()
                            ->defaultValue('.trackTitle[itemprop=name]')
                        ->end()
                        ->scalarNode('tracks')
                            ->cannotBeEmpty()
                            ->defaultValue('#track_table .title a[itemprop=url]')
                        ->end()
                        ->scalarNode('cover')
                            ->cannotBeEmpty()
                            ->defaultValue('#tralbumArt img[itemprop=image]')
                        ->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
