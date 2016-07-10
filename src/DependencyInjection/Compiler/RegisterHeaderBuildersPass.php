<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Reference,
    Compiler\CompilerPassInterface
};

final class RegisterHeaderBuildersPass implements CompilerPassInterface
{
    private $action;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(
            'innmind_rest_server.response.header_builder.'.$this->action
        );
        $builders = [];

        foreach ($ids as $id => $tags) {
            $builders[] = new Reference($id);
        }

        $container
            ->getDefinition(sprintf(
                'innmind_rest_server.response.header_builder.%s_delegation',
                $this->action
            ))
            ->replaceArgument(0, $builders);
    }
}
