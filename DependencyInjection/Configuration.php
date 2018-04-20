<?php

namespace GetRepo\ProxyBundle\DependencyInjection;

use GetRepo\ProxyBundle\ProxyFinder;
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
        $rootNode = $treeBuilder->root('proxy');

        $rootNode
            ->children()
                ->arrayNode('default')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('check')
                            ->defaultTrue()
                        ->end()
                        ->integerNode('timeout')
                            ->defaultValue(10)
                        ->end()
                        ->integerNode('tries')
                            ->defaultValue(3)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sites')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('url')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->booleanNode('check')
                                ->defaultNull()
                            ->end()
                            ->integerNode('timeout')
                                ->defaultNull()
                            ->end()
                            ->integerNode('tries')
                                ->defaultNull()
                            ->end()
                            ->enumNode('type')
                                ->values([
                                    ProxyFinder::TYPE_JSON,
                                    ProxyFinder::TYPE_STRING
                                ])
                                ->defaultValue(ProxyFinder::TYPE_JSON)
                            ->end()
                            ->arrayNode('paths')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('ip')
                                        ->cannotBeEmpty()
                                        ->defaultValue('ip')
                                    ->end()
                                    ->scalarNode('port')
                                        ->cannotBeEmpty()
                                        ->defaultValue('port')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('filters')
                                ->prototype('scalar')->end()
                                ->validate()
                                    ->ifTrue(function ($filters) {
                                        foreach ($filters as $k => $v) {
                                            if (!in_array($k, (array) ProxyFinder::FILTERS)) {
                                                return true;
                                            }
                                        }
                                    })
                                    ->thenInvalid('Invalid filter key')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}