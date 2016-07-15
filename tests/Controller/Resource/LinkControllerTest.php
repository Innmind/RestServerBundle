<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Controller\Resource\LinkController;
use Innmind\Rest\Server\{
    Response\HeaderBuilder\LinkBuilderInterface,
    Definition\Locator,
    Definition\Directory
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\ResponseInterface,
    Message\Method,
    ProtocolVersion,
    Headers,
    Header\HeaderInterface,
    Header\HeaderValueInterface,
    Header\Link,
    Header\LinkValue,
    Header\ParameterInterface,
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
    Routing\RouterInterface,
    HttpFoundation\Request
};

class LinkControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        $this->buildContainer();
    }

    public function testDefault()
    {
        $controller = $this->container->get(
            'innmind_rest_server.controller.resource.link'
        );
        $called = false;
        $this
            ->container
            ->get('gateway.command.link')
            ->method('__invoke')
            ->will($this->returnCallback(function($from, $tos) use (&$called) {
                $called = true;
                $this->assertSame('foo', (string) $from->identity());
                $this->assertCount(1, $tos);
                $this->assertSame('bar', (string) $tos->keys()->first()->identity());
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
                        new Method('LINK'),
                        $protocol = new ProtocolVersion(1, 1),
                        new Headers(
                            (new Map('string', HeaderInterface::class))
                                ->put(
                                    'Link',
                                    new Link(
                                        (new Set(HeaderValueInterface::class))
                                            ->add(
                                                new LinkValue(
                                                    Url::fromString('/top_dir/sub_dir/res/bar'),
                                                    'rel',
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
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', (string) $response->reasonPhrase());
        $this->assertSame($protocol, $response->protocolVersion());
        $this->assertCount(0, $response->headers());
        $this->assertSame('', (string) $response->body());
        $this->assertTrue($called);
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidGatewayMap()
    {
        new LinkController(
            new Map('string', 'string'),
            $this->createMock(LinkBuilderInterface::class),
            $this->createMock(RouterInterface::class),
            new Locator(new Map('string', Directory::class))
        );
    }
}
