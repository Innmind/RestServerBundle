<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection;

use Innmind\Rest\Server\Action;
use Symfony\Component\Config\Definition\{
    Builder\TreeBuilder,
    Builder\NodeBuilder,
    ConfigurationInterface
};

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder;
        $root = $treeBuilder->root('innmind_rest_server');

        $root
            ->children()
                ->arrayNode('types')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('accept')
                    ->info('The list of formats you accept in the "Accept" header')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->integerNode('priority')->end()
                            ->arrayNode('media_types')
                                ->useAttributeAsKey('name')
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('content_type')
                    ->info('The list of formats you support as content output')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->integerNode('priority')->end()
                            ->arrayNode('media_types')
                                ->useAttributeAsKey('name')
                                ->requiresAtLeastOneElement()
                                ->prototype('integer')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('specification_builder')
                    ->defaultValue('innmind_rest_server.specification_builder.default')
                ->end()
                ->scalarNode('range_extractor')
                    ->defaultValue('innmind_rest_server.range_extractor.delegation')
                ->end()
                ->arrayNode('response')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('header_builders')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode(Action::LIST)
                                    ->defaultValue('innmind_rest_server.response.header_builder.list_delegation')
                                ->end()
                                ->scalarNode(Action::GET)
                                    ->defaultValue('innmind_rest_server.response.header_builder.get_delegation')
                                ->end()
                                ->scalarNode(Action::CREATE)
                                    ->defaultValue('innmind_rest_server.response.header_builder.create_delegation')
                                ->end()
                                ->scalarNode(Action::UPDATE)
                                    ->defaultValue('innmind_rest_server.response.header_builder.update_delegation')
                                ->end()
                                ->scalarNode(Action::REMOVE)
                                    ->defaultValue('innmind_rest_server.response.header_builder.remove_delegation')
                                ->end()
                                ->scalarNode(Action::LINK)
                                    ->defaultValue('innmind_rest_server.response.header_builder.link_delegation')
                                ->end()
                                ->scalarNode(Action::UNLINK)
                                    ->defaultValue('innmind_rest_server.response.header_builder.unlink_delegation')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
