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
    Message\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Message\ResponseInterface,
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Query\ParameterInterface as QueryParameterInterface,
    Message\Form,
    Message\Form\ParameterInterface as FormParameterInterface,
    Message\Files,
    File\FileInterface,
    Headers,
    Header\HeaderInterface,
    Header\ParameterInterface,
    Header\Accept,
    Header\AcceptValue,
    Header\ContentType,
    Header\ContentTypeValue,
    Header\HeaderValueInterface
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

class FormatTest extends \PHPUnit_Framework_TestCase
{
    private $format;

    public function setUp()
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
                    (new Map('string', HeaderInterface::class))
                        ->put(
                            'Accept',
                            new Accept(
                                (new Set(HeaderValueInterface::class))
                                    ->add(
                                        new AcceptValue(
                                            'application',
                                            'json',
                                            new Map('string', ParameterInterface::class)
                                        )
                                    )
                            )
                        )
                ),
                new StringStream(''),
                new Environment(new Map('string', 'scalar')),
                new Cookies(new Map('string', 'scalar')),
                new Query(new Map('string', QueryParameterInterface::class)),
                new Form(new Map('scalar', FormParameterInterface::class)),
                new Files(new Map('string', FileInterface::class))
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
                    (new Map('string', HeaderInterface::class))
                        ->put(
                            'Accept',
                            new Accept(
                                (new Set(HeaderValueInterface::class))
                                    ->add(
                                        new AcceptValue(
                                            '*',
                                            '*',
                                            new Map('string', ParameterInterface::class)
                                        )
                                    )
                            )
                        )
                ),
                new StringStream(''),
                new Environment(new Map('string', 'scalar')),
                new Cookies(new Map('string', 'scalar')),
                new Query(new Map('string', QueryParameterInterface::class)),
                new Form(new Map('scalar', FormParameterInterface::class)),
                new Files(new Map('string', FileInterface::class))
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
                    (new Map('string', HeaderInterface::class))
                        ->put(
                            'Content-Type',
                            new ContentType(
                                new ContentTypeValue(
                                    'application',
                                    'json',
                                    new Map('string', ParameterInterface::class)
                                )
                            )
                        )
                ),
                new StringStream(''),
                new Environment(new Map('string', 'scalar')),
                new Cookies(new Map('string', 'scalar')),
                new Query(new Map('string', QueryParameterInterface::class)),
                new Form(new Map('scalar', FormParameterInterface::class)),
                new Files(new Map('string', FileInterface::class))
            )
        );

        $this->assertInstanceOf(FormatFormat::class, $format);
        $this->assertSame('json', $format->name());
        $this->assertSame('application/json', (string) $format->preferredMediaType());
    }
}
