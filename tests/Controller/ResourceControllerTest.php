<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller;

use Innmind\Rest\ServerBundle\{
    InnmindRestServerBundle,
    DependencyInjection\InnmindRestServerExtension
};
use Innmind\Rest\Server\{
    Serializer\Normalizer\DefinitionNormalizer,
    ResourceListAccessorInterface,
    ResourceAccessorInterface,
    ResourceCreatorInterface,
    ResourceUpdaterInterface,
    ResourceRemoverInterface,
    ResourceLinkerInterface,
    ResourceUnlinkerInterface,
    Gateway,
    IdentityInterface,
    Identity
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Message\ResponseInterface,
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Query\ParameterInterface as QueryParameterInterface,
    Message\Query\Parameter as QueryParameter,
    Message\Form,
    Message\Form\ParameterInterface as FormParameterInterface,
    Message\Files,
    File\FileInterface,
    Headers,
    Header\HeaderInterface,
    Header\HeaderValueInterface,
    Header\Accept,
    Header\AcceptValue,
    Header\Range,
    Header\RangeValue,
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
    DependencyInjection\Reference,
    Routing\RouterInterface,
    Serializer\Serializer,
    Serializer\Encoder\JsonEncoder,
    HttpFoundation\Request
};
use Symfony\Bundle\FrameworkBundle\{
    FrameworkBundle,
    DependencyInjection\FrameworkExtension
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
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.cache_dir', sys_get_temp_dir());
        $this->container->setParameter('kernel.charset', 'utf-8');
        $this->container->setParameter('kernel.root_dir', __DIR__.'/../');
        $this->container->setParameter('kernel.secret', 'foo');
        $this->container->setParameter('kernel.container_class', ContainerBuilder::class);
        $this->container->setDefinition(
            'router',
            new Definition(RouterInterface::class)
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
                ResourceListAccessorInterface::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.get',
            new Definition(get_class($this->createMock(
                ResourceAccessorInterface::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.create',
            new Definition(get_class($this->createMock(
                ResourceCreatorInterface::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.update',
            new Definition(get_class($this->createMock(
                ResourceUpdaterInterface::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.remove',
            new Definition(get_class($this->createMock(
                ResourceRemoverInterface::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.link',
            new Definition(get_class($this->createMock(
                ResourceLinkerInterface::class
            )))
        );
        $this->container->setDefinition(
            'gateway.command.unlink',
            new Definition(get_class($this->createMock(
                ResourceUnlinkerInterface::class
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
        (new FrameworkExtension)->load([], $this->container);
        (new FrameworkBundle)->build($this->container);
        (new InnmindRestServerBundle)->build($this->container);
        $this->container->compile();

        $this->controller = $this->container->get(
            'innmind_rest_server.controller.resource'
        );

        $this
            ->container
            ->get('gateway.command.list')
            ->method('__invoke')
            ->will($this->returnCallback(function($definition, $spec, $range) {
                $identities = new Set(IdentityInterface::class);

                if ($range !== null && $range->lastPosition() === 42) {
                    return $identities;
                }

                return $identities->add(new Identity(42));
            }));
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

    public function testListAction()
    {
        $response = $this->controller->listAction(
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
                                ->put(
                                    'Range',
                                    new Range(
                                        new RangeValue('resources', 0, 2)
                                    )
                                )
                        ),
                        new StringStream(''),
                        new Environment(new Map('string', 'scalar')),
                        new Cookies(new Map('string', 'scalar')),
                        new Query(
                            (new Map('string', QueryParameterInterface::class))
                                ->put(
                                    'url',
                                    new QueryParameter('url', 'foo')
                                )
                        ),
                        new Form(new Map('scalar', FormParameterInterface::class)),
                        new Files(new Map('string', FileInterface::class))
                    )
                ]
            )
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(206, $response->statusCode()->value());
        $this->assertSame('Partial Content', (string) $response->reasonPhrase());
        $this->assertSame($protocol, $response->protocolVersion());
        $this->assertCount(4, $response->headers());
        $this->assertSame(
            'Content-Type : application/json',
            (string) $response->headers()->get('content-type')
        );
        $this->assertSame(
            'Link : </42>; rel="resource"',
            (string) $response->headers()->get('link')
        );
        $this->assertSame(
            'Accept-Ranges : resources',
            (string) $response->headers()->get('accept-ranges')
        );
        $this->assertSame(
            'Content-Range : resources 0-1/1',
            (string) $response->headers()->get('content-range')
        );
        $this->assertSame('{"identities":[42]}', (string) $response->body());
    }

    public function testListActionWithoutRange()
    {
        $response = $this->controller->listAction(
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
                        new Query(
                            (new Map('string', QueryParameterInterface::class))
                                ->put(
                                    'url',
                                    new QueryParameter('url', 'foo')
                                )
                        ),
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
        $this->assertCount(3, $response->headers());
        $this->assertSame(
            'Content-Type : application/json',
            (string) $response->headers()->get('content-type')
        );
        $this->assertSame(
            'Link : </42>; rel="resource"',
            (string) $response->headers()->get('link')
        );
        $this->assertSame(
            'Accept-Ranges : resources',
            (string) $response->headers()->get('accept-ranges')
        );
        $this->assertSame('{"identities":[42]}', (string) $response->body());
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\RangeNotSatisfiableException
     */
    public function testThrowWhenNoIdentityForExpectedRange()
    {
        $response = $this->controller->listAction(
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
                                ->put(
                                    'Range',
                                    new Range(
                                        new RangeValue('resources', 0, 42)
                                    )
                                )
                        ),
                        new StringStream(''),
                        new Environment(new Map('string', 'scalar')),
                        new Cookies(new Map('string', 'scalar')),
                        new Query(
                            (new Map('string', QueryParameterInterface::class))
                                ->put(
                                    'url',
                                    new QueryParameter('url', 'foo')
                                )
                        ),
                        new Form(new Map('scalar', FormParameterInterface::class)),
                        new Files(new Map('string', FileInterface::class))
                    )
                ]
            )
        );
    }
}
