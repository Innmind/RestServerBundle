<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection;

use Innmind\Rest\ServerBundle\{
    DependencyInjection\InnmindRestServerExtension,
    InnmindRestServerBundle
};
use Innmind\Rest\Server\Format\Format;
use Innmind\Immutable\MapInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InnmindRestServerExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', []);
        $extension = new InnmindRestServerExtension;

        $this->assertSame(
            null,
            $extension->load(
                [[
                    'types' => ['foo'],
                    'accept' => [
                        'json' => [
                            'priority' => 0,
                            'media_types' => [
                                'application/json' => 0,
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
        $accept = $container->getDefinition(
            'innmind_rest_server.formats.accept'
        );
        $accept = $accept->getArgument(0);
        $this->assertInstanceOf(MapInterface::class, $accept);
        $this->assertSame('string', (string) $accept->keyType());
        $this->assertSame(Format::class, (string) $accept->valueType());
        $this->assertSame(1, $accept->size());
        $this->assertSame('json', $accept->key());
        $this->assertSame(
            'json',
            $accept->current()->name()
        );
        $this->assertSame(0, $accept->current()->priority());
        $this->assertSame(
            'application/json',
            (string) $accept->current()->mediaTypes()->current()
        );
        $this->assertSame(
            0,
            $accept->current()->mediaTypes()->current()->priority()
        );
        $contentType = $container->getDefinition(
            'innmind_rest_server.formats.content_type'
        );
        $contentType = $contentType->getArgument(0);
        $this->assertInstanceOf(MapInterface::class, $contentType);
        $this->assertSame('string', (string) $contentType->keyType());
        $this->assertSame(Format::class, (string) $contentType->valueType());
        $this->assertSame(1, $contentType->size());
        $this->assertSame('json', $contentType->key());
        $this->assertSame(
            'json',
            $contentType->current()->name()
        );
        $this->assertSame(0, $contentType->current()->priority());
        $this->assertSame(
            'application/json',
            (string) $contentType->current()->mediaTypes()->current()
        );
        $this->assertSame(
            0,
            $contentType->current()->mediaTypes()->current()->priority()
        );
    }
}
