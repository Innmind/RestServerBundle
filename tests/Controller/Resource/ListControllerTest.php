<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Controller\Resource\ListController;
use Innmind\Rest\Server\{
    Identity as IdentityInterface,
    Identity\Identity,
    RangeExtractor\Extractor,
    SpecificationBuilder\Builder,
    Response\HeaderBuilder\ListBuilder
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Message\Response,
    Message\Environment\Environment,
    Message\Cookies\Cookies,
    Message\Query\Query,
    Message\Query\Parameter as QueryParameterInterface,
    Message\Query\Parameter\Parameter as QueryParameter,
    Message\Form\Form,
    Message\Files\Files,
    Headers\Headers,
    Header,
    Header\Accept,
    Header\AcceptValue,
    Header\Range,
    Header\RangeValue
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    Map,
    Set
};
use Symfony\Component\{
    HttpFoundation\Request,
    Serializer\SerializerInterface
};

class ListControllerTest extends ControllerTestCase
{
    private $controller;

    public function setUp()
    {
        $this->buildContainer();
        $this->controller = $this->container->get(
            'innmind_rest_server.controller.resource.list'
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

    public function testDefaultAction()
    {
        $response = $this->controller->defaultAction(
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
                            (new Map('string', Header::class))
                                ->put(
                                    'Accept',
                                    new Accept(
                                        new AcceptValue(
                                            'application',
                                            'json'
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
                        new Environment,
                        new Cookies,
                        new Query(
                            (new Map('string', QueryParameterInterface::class))
                                ->put(
                                    'url',
                                    new QueryParameter('url', 'foo')
                                )
                        ),
                        new Form,
                        new Files
                    )
                ]
            )
        );

        $this->assertInstanceOf(Response::class, $response);
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

    public function testDefaultActionWithoutRange()
    {
        $response = $this->controller->defaultAction(
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
                            (new Map('string', Header::class))
                                ->put(
                                    'Accept',
                                    new Accept(
                                        new AcceptValue(
                                            'application',
                                            'json'
                                        )
                                    )
                                )
                        ),
                        new StringStream(''),
                        new Environment,
                        new Cookies,
                        new Query(
                            (new Map('string', QueryParameterInterface::class))
                                ->put(
                                    'url',
                                    new QueryParameter('url', 'foo')
                                )
                        ),
                        new Form,
                        new Files
                    )
                ]
            )
        );

        $this->assertInstanceOf(Response::class, $response);
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
     * @expectedException Innmind\Http\Exception\Http\RangeNotSatisfiable
     */
    public function testThrowWhenNoIdentityForExpectedRange()
    {
        $response = $this->controller->defaultAction(
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
                            (new Map('string', Header::class))
                                ->put(
                                    'Accept',
                                    new Accept(
                                        new AcceptValue(
                                            'application',
                                            'json'
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
                        new Environment,
                        new Cookies,
                        new Query(
                            (new Map('string', QueryParameterInterface::class))
                                ->put(
                                    'url',
                                    new QueryParameter('url', 'foo')
                                )
                        ),
                        new Form,
                        new Files
                    )
                ]
            )
        );
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidGatewayMap()
    {
        new ListController(
            $this->container->get('innmind_rest_server.format'),
            $this->createMock(SerializerInterface::class),
            $this->createMock(Extractor::class),
            $this->createMock(Builder::class),
            new Map('int', 'int'),
            $this->createMock(ListBuilder::class)
        );
    }
}
