<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Controller\Resource\GetController;
use Innmind\Rest\Server\{
    HttpResource\HttpResource,
    HttpResource\Property,
    Response\HeaderBuilder\GetBuilder
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Message\Response,
    Message\Environment\Environment,
    Message\Cookies\Cookies,
    Message\Query\Query,
    Message\Form\Form,
    Message\Files\Files,
    Headers\Headers,
    Header,
    Header\Accept,
    Header\AcceptValue
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\Map;
use Symfony\Component\{
    HttpFoundation\Request,
    Serializer\SerializerInterface
};

class GetControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        $this->buildContainer();
    }

    public function testDefaultAction()
    {
        $controller = $this->container->get(
            'innmind_rest_server.controller.resource.get'
        );

        $this
            ->container
            ->get('gateway.command.get')
            ->method('__invoke')
            ->willReturn(new HttpResource(
                $this
                    ->container
                    ->get('innmind_rest_server.definition.directories')
                    ->get('top_dir')
                    ->definitions()
                    ->get('image'),
                (new Map('string', Property::class))
                    ->put('uuid', new Property('uuid', 'foo'))
                    ->put('url', new Property('url', 'example.com'))
            ));
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
                        new Query,
                        new Form,
                        new Files
                    )
                ]
            ),
            'foo'
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', (string) $response->reasonPhrase());
        $this->assertSame($protocol, $response->protocolVersion());
        $this->assertCount(1, $response->headers());
        $this->assertSame(
            'Content-Type : application/json',
            (string) $response->headers()->get('content-type')
        );
        $this->assertSame(
            '{"resource":{"uuid":"foo","url":"example.com"}}',
            (string) $response->body()
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 3 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>
     */
    public function testThrowWhenInvalidGatewayMap()
    {
        new GetController(
            $this->container->get('innmind_rest_server.format'),
            $this->createMock(SerializerInterface::class),
            new Map('int', 'int'),
            $this->createMock(GetBuilder::class)
        );
    }
}
