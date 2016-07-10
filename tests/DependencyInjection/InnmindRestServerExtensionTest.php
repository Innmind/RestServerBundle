<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection;

use Innmind\Rest\ServerBundle\{
    DependencyInjection\InnmindRestServerExtension,
    InnmindRestServerBundle
};
use Innmind\Rest\Server\Format\Format;
use Innmind\Immutable\MapInterface;
use Symfony\Component\{
    DependencyInjection\ContainerBuilder,
    DependencyInjection\Definition,
    Routing\RouterInterface,
    Serializer\Serializer
};

class InnmindRestServerExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', []);
        $container->setDefinition(
            'router',
            new Definition(RouterInterface::class)
        );
        $container->setDefinition(
            'serializer',
            new Definition(Serializer::class)
        );
        $extension = new InnmindRestServerExtension;

        $this->assertSame(
            null,
            $extension->load(
                [[
                    'types' => ['foo'],
                    'accept' => $accept = [
                        'json' => [
                            'priority' => 0,
                            'media_types' => [
                                'application/json' => 0,
                            ],
                        ],
                    ],
                    'content_type' => $contentType = [
                        'json' => [
                            'priority' => 0,
                            'media_types' => [
                                'application/json' => 0,
                            ],
                        ],
                    ],
                ]],
                $container
            )
        );
        (new InnmindRestServerBundle)->build($container);
        $container->compile();
        $types = $container->getDefinition('innmind_rest_server.definition.types');
        $this->assertSame(1, count($types->getMethodCalls()));
        $this->assertSame(
            ['register', ['foo']],
            $types->getMethodCalls()[0]
        );
        $this->assertSame(
            $accept,
            $container
                ->getDefinition('innmind_rest_server.formats.accept')
                ->getArgument(0)
        );
        $this->assertSame(
            $contentType,
            $container
                ->getDefinition('innmind_rest_server.formats.content_type')
                ->getArgument(0)
        );
    }
}
