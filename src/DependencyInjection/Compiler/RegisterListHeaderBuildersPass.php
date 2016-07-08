<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Reference,
    Compiler\CompilerPassInterface
};

final class RegisterListHeaderBuildersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(
            'innmind_rest_server.response.header_builder.list'
        );
        $builders = [];

        foreach ($ids as $id => $tags) {
            $builders[] = new Reference($id);
        }

        $container
            ->getDefinition(
                'innmind_rest_server.response.header_builder.list_delegation'
            )
            ->replaceArgument(0, $builders);
    }
}
