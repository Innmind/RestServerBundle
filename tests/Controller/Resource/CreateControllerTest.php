<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Controller\Resource\CreateController;
use Innmind\Rest\Server\{
    Identity\Identity,
    Response\HeaderBuilder\CreateBuilder
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Response,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
    Header,
    Header\Accept,
    Header\AcceptValue,
    Header\ContentType,
    Header\ContentTypeValue,
    Message\Environment\Environment,
    Message\Cookies\Cookies,
    Message\Query\Query,
    Message\Form\Form,
    Message\Files\Files
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\Map;
use Symfony\Component\{
    HttpFoundation\Request,
    Serializer\SerializerInterface
};

class CreateControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        $this->buildContainer();
    }

    public function testDefaultAction()
    {
        $controller = $this->container->get(
            'innmind_rest_server.controller.resource.create'
        );
        $this
            ->container
            ->get('gateway.command.create')
            ->method('__invoke')
            ->will($this->returnCallback(function($definition, $resource) {
                $this->assertSame($definition, $resource->definition());
                $this->assertSame('example.com', $resource->property('url')->value());

                return new Identity(42);
            }));
        $response = $controller->defaultAction(
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
                        new Method('POST'),
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
                                    'Content-Type',
                                    new ContentType(
                                        new ContentTypeValue(
                                            'application',
                                            'json'
                                        )
                                    )
                                )
                        ),
                        new StringStream('{"resource":{"url":"example.com"}}'),
                        new Environment,
                        new Cookies,
                        new Query,
                        new Form,
                        new Files
                    )
                ]
            )
        );

        $this->assertInstanceOf(Response::class, $response);
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

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>
     */
    public function testThrowWhenInvalidGatewayMap()
    {
        new CreateController(
            new Map('int', 'int'),
            $this->createMock(SerializerInterface::class),
            $this->container->get('innmind_rest_server.format'),
            $this->createMock(CreateBuilder::class)
        );
    }
}
