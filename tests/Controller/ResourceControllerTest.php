<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller;

use Innmind\Rest\ServerBundle\{
    InnmindRestServerBundle,
    DependencyInjection\InnmindRestServerExtension,
    Controller\ResourceController,
    Format
};
use Innmind\Rest\Server\Serializer\Normalizer\DefinitionNormalizer;
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
    Header\HeaderValueInterface,
    Header\Accept,
    Header\AcceptValue,
    Header\ParameterInterface
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
    Serializer\Serializer,
    Serializer\Encoder\JsonEncoder,
    HttpFoundation\Request
};
use Fixtures\Innmind\Rest\ServerBundle\FixtureBundle\FixtureFixtureBundle;

class ResourceControllerTest extends \PHPUnit_Framework_TestCase
{
    private $controller;
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder;
        $this->container->setParameter('kernel.bundles', [
            'FixtureFixtureBundle' => FixtureFixtureBundle::class,
        ]);
        $this->container->setDefinition(
            'router',
            new Definition(RouterInterface::class)
        );
        $this->container->setDefinition(
            'serializer',
            new Definition(Serializer::class)
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
        (new InnmindRestServerBundle)->build($this->container);
        $this->container->compile();

        $format = new Format(
            $this->container->get('innmind_rest_server.formats.accept'),
            $this->container->get('innmind_rest_server.formats.content_type')
        );

        $this->controller = new ResourceController(
            $format,
            new Serializer(
                [new DefinitionNormalizer],
                [new JsonEncoder]
            )
        );
    }

    public function testOptionsAction()
    {
        $response = $this->controller->optionsAction(
            new Request(
                [],
                [],
                [
                    '_innmind_resource_definition' => $this
                        ->container
                        ->get('innmind_rest_server.definition.directories')
                        ->get('top_dir')
                        ->definitions()
                        ->get('image'),
                    '_innmind_request' => new ServerRequest(
                        Url::fromString('/'),
                        new Method('GET'),
                        $protocol = new ProtocolVersion(1, 1),
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
                                                    new Map(
                                                        'string',
                                                        ParameterInterface::class
                                                    )
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
                ]
            )
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', (string) $response->reasonPhrase());
        $this->assertSame($protocol, $response->protocolVersion());
        $this->assertSame(1, $response->headers()->count());
        $this->assertSame(
            'Content-Type : application/json',
            (string) $response->headers()->get('content-type')
        );
        $this->assertSame(
            json_encode([
                'identity' => 'uuid',
                'properties' => [
                    'uuid' => [
                        'type' => 'string',
                        'access' => ['READ'],
                        'variants' => [],
                        'optional' => false,
                    ],
                    'url' => [
                        'type' => 'string',
                        'access' => ['READ', 'CREATE', 'UPDATE'],
                        'variants' => [],
                        'optional' => false,
                    ],
                ],
                'metas' => [],
                'rangeable' => true,
            ]),
            (string) $response->body()
        );
    }
}
