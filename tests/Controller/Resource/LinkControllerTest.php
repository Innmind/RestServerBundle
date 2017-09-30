<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\{
    Controller\Resource\LinkController,
    Translator\LinkTranslator
};
use Innmind\Rest\Server\{
    Response\HeaderBuilder\LinkBuilder,
    Definition\Locator,
    Definition\Directory
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Response,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
    Header,
    Header\Link,
    Header\LinkValue,
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
        $this
            ->container
            ->get('gateway.command.link')
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(function($from) {
                    return (string) $from->identity() === 'foo';
                }),
                $this->callback(function($tos) {
                    return $tos->size() === 1 &&
                        (string) $tos->keys()->current()->identity() === 'bar';
                })
            );
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
                            (new Map('string', Header::class))
                                ->put(
                                    'Link',
                                    new Link(
                                        new LinkValue(
                                            Url::fromString('/top_dir/sub_dir/res/bar'),
                                            'rel'
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
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', (string) $response->reasonPhrase());
        $this->assertSame($protocol, $response->protocolVersion());
        $this->assertCount(0, $response->headers());
        $this->assertSame('', (string) $response->body());
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\BadRequest
     */
    public function testThrowWhenNoLinkHeader()
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
                        new Headers(new Map('string', Header::class)),
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
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>
     */
    public function testThrowWhenInvalidGatewayMap()
    {
        new LinkController(
            new Map('string', 'string'),
            $this->createMock(LinkBuilder::class),
            new LinkTranslator(
                new Locator(new Map('string', Directory::class)),
                $this->createMock(RouterInterface::class)
            )
        );
    }
}
