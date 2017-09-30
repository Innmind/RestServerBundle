<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\Server\Action;
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Reference,
    Compiler\CompilerPassInterface
};

final class RegisterHeaderBuildersPass implements CompilerPassInterface
{
    private $action;

    public function __construct(Action $action)
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
        $definition = $container->getDefinition(sprintf(
            'innmind_rest_server.response.header_builder.%s_delegation',
            $this->action
        ));

        foreach ($ids as $id => $tags) {
            $definition->addArgument(new Reference($id));
        }
    }
}
