<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\Exception\MissingAlias;
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface,
    Reference
};

final class RegisterHttpHeaderFactoriesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(
            'innmind_rest_server.http_header_factory'
        );
        $factories = [];

        foreach ($ids as $id => $tags) {
            foreach ($tags as $tag => $attributes) {
                if (!isset($attributes['alias'])) {
                    throw new MissingAlias;
                }

                $factories[$attributes['alias']] = new Reference($id);
            }
        }

        $container
            ->getDefinition('innmind_rest_server.http.factory.header.default')
            ->addArgument($factories);
    }
}
