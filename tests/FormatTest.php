<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle;

use Innmind\Rest\ServerBundle\{
    DependencyInjection\InnmindRestServerExtension,
    InnmindRestServerBundle,
    Format
};
use Innmind\Rest\Server\Format\Format as FormatFormat;
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Message\Environment\Environment,
    Message\Cookies\Cookies,
    Message\Query\Query,
    Message\Form\Form,
    Message\Files\Files,
    Headers\Headers,
    Header,
    Header\Accept,
    Header\AcceptValue,
    Header\ContentType,
    Header\ContentTypeValue,
    Header\Value
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    Map,
    Set
};
use Symfony\Component\{
    DependencyInjection\ContainerBuilder,
    DependencyInjection\Definition,
    Routing\RouterInterface,
    Serializer\Serializer
};
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    private $format;

    public function setUp()
    {
        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.debug', true);
        $container->setDefinition(
            'router',
            new Definition(RouterInterface::class)
        );
        $container->setDefinition(
            'serializer',
            new Definition(Serializer::class)
        );
        $extension = new InnmindRestServerExtension;

        $extension->load(
            [[
                'types' => ['foo'],
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
            $container
        );
        (new InnmindRestServerBundle)->build($container);
        $container->compile();

        $this->format = new Format(
            $container->get('innmind_rest_server.formats.accept'),
            $container->get('innmind_rest_server.formats.content_type')
        );
    }

    public function testAcceptable()
    {
        $format = $this->format->acceptable(
            new ServerRequest(
                Url::fromString('/'),
                new Method('GET'),
                new ProtocolVersion(1, 1),
                new Headers(
                    (new Map('string', Header::class))
                        ->put(
                            'Accept',
                            new Accept(
                                new AcceptValue('application', 'json')
                            )
                        )
                ),
                new StringStream(''),
                new Environment,
                new Cookies,
                new Query,
                new Form,
                new Files
            )
        );

        $this->assertInstanceOf(FormatFormat::class, $format);
        $this->assertSame('json', $format->name());
        $this->assertSame('application/json', (string) $format->preferredMediaType());
    }

    public function testAcceptableWhenAcceptingEverything()
    {
        $format = $this->format->acceptable(
            new ServerRequest(
                Url::fromString('/'),
                new Method('GET'),
                new ProtocolVersion(1, 1),
                new Headers(
                    (new Map('string', Header::class))
                        ->put(
                            'Accept',
                            new Accept(
                                new AcceptValue('*', '*')
                            )
                        )
                ),
                new StringStream(''),
                new Environment,
                new Cookies,
                new Query,
                new Form,
                new Files
            )
        );

        $this->assertInstanceOf(FormatFormat::class, $format);
        $this->assertSame('json', $format->name());
        $this->assertSame('application/json', (string) $format->preferredMediaType());
    }

    public function testContentType()
    {
        $format = $this->format->contentType(
            new ServerRequest(
                Url::fromString('/'),
                new Method('GET'),
                new ProtocolVersion(1, 1),
                new Headers(
                    (new Map('string', Header::class))
                        ->put(
                            'Content-Type',
                            new ContentType(
                                new ContentTypeValue('application', 'json')
                            )
                        )
                ),
                new StringStream(''),
                new Environment,
                new Cookies,
                new Query,
                new Form,
                new Files
            )
        );

        $this->assertInstanceOf(FormatFormat::class, $format);
        $this->assertSame('json', $format->name());
        $this->assertSame('application/json', (string) $format->preferredMediaType());
    }
}
