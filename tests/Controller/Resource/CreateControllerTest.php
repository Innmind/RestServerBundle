<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\Server\Identity;
use Innmind\Http\{
    Message\ServerRequest,
    Message\ResponseInterface,
    Message\Method,
    ProtocolVersion,
    Headers,
    Header\HeaderInterface,
    Header\Accept,
    Header\AcceptValue,
    Header\HeaderValueInterface,
    Header\ParameterInterface,
    Header\ContentType,
    Header\ContentTypeValue,
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Query\ParameterInterface as QueryParameterInterface,
    Message\Form,
    Message\Form\ParameterInterface as FormParameterInterface,
    Message\Files,
    File\FileInterface
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    Map,
    Set
};
use Symfony\Component\HttpFoundation\Request;

class CreateControllerTest extends ControllerTestCase
{
    private $controller;

    public function setUp()
    {
        $this->buildContainer();
        $this->controller = $this->container->get(
            'innmind_rest_server.controller.resource.create'
        );
        $this
            ->container
            ->get('gateway.command.create')
            ->method('__invoke')
            ->willReturn(new Identity(42));
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
                                    'Content-Type',
                                    new ContentType(
                                        new ContentTypeValue(
                                            'application',
                                            'json',
                                            new Map(
                                                'string',
                                                ParameterInterface::class
                                            )
                                        )
                                    )
                                )
                        ),
                        new StringStream('{"resource":{"url":"example.com"}}'),
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
        $this->assertSame(201, $response->statusCode()->value());
        $this->assertSame('Created', (string) $response->reasonPhrase());
        $this->assertSame($protocol, $response->protocolVersion());
        $this->assertCount(2, $response->headers());
        $this->assertSame(
            'Content-Type : application/json',
            (string) $response->headers()->get('Content-Type')
        );
        $this->assertSame(
            'Location : /42',
            (string) $response->headers()->get('Location')
        );
        $this->assertSame(
            '{"identity":42}',
            (string) $response->body()
        );
    }
}
