<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Reference,
    Compiler\CompilerPassInterface
};

final class RegisterRangeExtractorsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(
            'innmind_rest_server.range_extractor'
        );
        $definition = $container->getDefinition('innmind_rest_server.range_extractor.delegation');

        foreach ($ids as $id => $tags) {
            $definition->addArgument(new Reference($id));
        }
    }
}
