<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\Exception\{
    MissingPriorityException,
    PriorityAlreadyUsedByAVerifierException
};
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface,
    Reference
};

final class RegisterRequestVerifiersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(
            'innmind_rest_server.http.request.verifier'
        );
        $definition = $container->getDefinition('innmind_rest_server.http.request.verifier');
        $verifiers = [];

        foreach ($ids as $id => $tags) {
            foreach ($tags as $tag => $attributes) {
                if (!isset($attributes['priority'])) {
                    throw new MissingPriorityException;
                }

                $priority = (int) $attributes['priority'];

                if (isset($verifiers[$priority])) {
                    throw new PriorityAlreadyUsedByAVerifierException(
                        (string) $priority
                    );
                }

                $verifiers[$priority] = new Reference($id);
            }
        }

        krsort($verifiers);

        foreach ($verifiers as $verifier) {
            $definition->addArgument($verifier);
        }
    }
}
