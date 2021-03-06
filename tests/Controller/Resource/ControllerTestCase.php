<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\{
    InnmindRestServerBundle,
    DependencyInjection\InnmindRestServerExtension
};
use Innmind\Rest\Server\{
    ResourceListAccessor,
    ResourceAccessor,
    ResourceCreator,
    ResourceUpdater,
    ResourceRemover,
    ResourceLinker,
    ResourceUnlinker,
    Gateway\Gateway
};
use Symfony\Component\{
    DependencyInjection\ContainerBuilder,
    DependencyInjection\Definition,
    DependencyInjection\Reference,
    Routing\Router,
    Serializer\Serializer,
    Serializer\Encoder\JsonEncoder
};
use Symfony\Bundle\FrameworkBundle\{
    FrameworkBundle,
    DependencyInjection\FrameworkExtension
};
use Fixtures\Innmind\Rest\ServerBundle\FixtureBundle\FixtureFixtureBundle;
use PHPUnit\Framework\TestCase;

abstract class ControllerTestCase extends TestCase
{
    protected $container;

    protected function buildContainer()
    {
        if ($this->container instanceof ContainerBuilder) {
            return;
        }

        $this->container = new ContainerBuilder;
        $this->container->setParameter('kernel.bundles', [
            'FixtureFixtureBundle' => FixtureFixtureBundle::class,
        ]);
        $this->container->setParameter('kernel.bundles_metadata', [
            'FixtureFixtureBundle' => [
                'path' => __DIR__.'/../../../fixtures/FixtureBundle',
            ],
        ]);
        $this->container->setParameter('kernel.name', 'idk');
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.cache_dir', sys_get_temp_dir());
        $this->container->setParameter('kernel.charset', 'utf-8');
        $this->container->setParameter('kernel.root_dir', __DIR__.'/../');
        $this->container->setParameter('kernel.secret', 'foo');
        $this->container->setParameter('kernel.container_class', ContainerBuilder::class);
        $this->container->setDefinition(
            'router',
            new Definition(
                Router::class,
                [
                    new Reference('innmind_rest_server.routing.route_loader'),
                    '.'
                ]
            )
        );
        $this->container->setDefinition(
            'serializer',
            new Definition(Serializer::class, [[], []])
        );
        $this->container->setDefinition(
            'serializer.encoder.json',
            (new Definition(JsonEncoder::class))->addTag('serializer.encoder')
        );
        $this->container->setDefinition(
            'gateway.command.list',
            new Definition(get_class($this->createMock(
                ResourceListAccessor::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.get',
            new Definition(get_class($this->createMock(
                ResourceAccessor::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.create',
            new Definition(get_class($this->createMock(
                ResourceCreator::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.update',
            new Definition(get_class($this->createMock(
                ResourceUpdater::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.remove',
            new Definition(get_class($this->createMock(
                ResourceRemover::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.link',
            new Definition(get_class($this->createMock(
                ResourceLinker::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.unlink',
            new Definition(get_class($this->createMock(
                ResourceUnlinker::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command',
            (new Definition(
                Gateway::class,
                [
                    new Reference('gateway.command.list'),
                    new Reference('gateway.command.get'),
                    new Reference('gateway.command.create'),
                    new Reference('gateway.command.update'),
                    new Reference('gateway.command.remove'),
                    new Reference('gateway.command.link'),
                    new Reference('gateway.command.unlink'),
                ]
            ))
                ->addTag('innmind_rest_server.gateway', ['alias' => 'command'])
        );
        $extension = new InnmindRestServerExtension;

        $extension->load(
            [[
                'accept' => [
                    'json' => [
                        'priority' => 10,
                        'media_types' => [
                            'application/json' => 0,
                        ],
                    ],
                    'html' => [
                        'priority' => 0,
                        'media_types' => [
                            'text/html' => 0,
                        ],
                    ],
                ],
                'content_type' => [
                    'json' => [
                        'priority' => 0,
                        'media_types' => [
                            'application/json' => 0,
                        ],
                    ],
                ],
            ]],
            $this->container
        );
        (new FrameworkExtension)->load(
            [['annotations' => ['enabled' => false]]],
            $this->container
        );
        (new FrameworkBundle)->build($this->container);
        (new InnmindRestServerBundle)->build($this->container);
        $this->container->compile();
    }
}
