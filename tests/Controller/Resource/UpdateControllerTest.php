<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Controller\Resource\UpdateController;
use Innmind\Rest\Server\Response\HeaderBuilder\UpdateBuilderInterface;
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
                            new StringStream('{"resource":{"url":"example.com/foo"}}'),
                            new Environment(new Map('string', 'scalar')),
                            new Cookies(new Map('string', 'scalar')),
                            new Query(new Map('string', QueryParameterInterface::class)),
                            new Form(new Map('scalar', FormParameterInterface::class)),
                            new Files(new Map('string', FileInterface::class))
                        )
                    ]
                ),
                'foo'
            );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', (string) $response->reasonPhrase());
        $this->assertCount(0, $response->headers());
        $this->assertSame('', (string) $response->body());
    }

    /**
     * @expectedException Innmind\rest\ServerBundle\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidGatewayMap()
    {
        new UpdateController(
            new Map('int', 'int'),
            $this->createMock(SerializerInterface::class),
            $this->container->get('innmind_rest_server.format'),
            $this->createMock(UpdateBuilderInterface::class)
        );
    }
}
