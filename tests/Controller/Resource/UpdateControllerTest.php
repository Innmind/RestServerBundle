<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Controller\Resource\UpdateController;
use Innmind\Rest\Server\Response\HeaderBuilder\UpdateBuilder;
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

class UpdateControllerTest extends ControllerTestCase
{
    private $controller;

    public function setUp()
    {
        $this->buildContainer();
    }

    public function testDefaultAction()
    {
        $response = $this
            ->container
            ->get(
                'innmind_rest_server.controller.resource.update'
            )
            ->defaultAction(
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
                            Url::fromString('/foo'),
                            new Method('PUT'),
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
                            new StringStream('{"resource":{"url":"example.com/foo"}}'),
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
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', (string) $response->reasonPhrase());
        $this->assertCount(0, $response->headers());
        $this->assertSame('', (string) $response->body());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>
     */
    public function testThrowWhenInvalidGatewayMap()
    {
        new UpdateController(
            new Map('int', 'int'),
            $this->createMock(SerializerInterface::class),
            $this->container->get('innmind_rest_server.format'),
            $this->createMock(UpdateBuilder::class)
        );
    }
}
