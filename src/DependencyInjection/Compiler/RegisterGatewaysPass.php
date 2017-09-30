<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\Exception\MissingAlias;
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface,
    Reference
};

final class RegisterGatewaysPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds('innmind_rest_server.gateway');
        $gateways = [];

        foreach ($ids as $id => $tags) {
            foreach ($tags as $tag => $attributes) {
                if (!isset($attributes['alias'])) {
                    throw new MissingAlias;
                }

                $gateways[$attributes['alias']] = new Reference($id);
            }
        }

        $container
            ->getDefinition('innmind_rest_server.gateways')
            ->addArgument($gateways);
    }
}
