<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection;

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
            ->end();

        return $treeBuilder;
    }
}
