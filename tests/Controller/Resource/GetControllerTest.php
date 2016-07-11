<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Controller\Resource\GetController;
use Innmind\Rest\Server\{
    HttpResource,
    Property,
    Response\HeaderBuilder\GetBuilderInterface
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
            ),
            'foo'
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
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
     * @expectedException Innmind\Rest\ServerBundle\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidGatewayMap()
    {
        new GetController(
            $this->container->get('innmind_rest_server.format'),
            $this->createMock(SerializerInterface::class),
            new Map('int', 'int'),
            $this->createMock(GetBuilderInterface::class)
        );
    }
}
