<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Controller\Resource\RemoveController;
use Innmind\Rest\Server\Response\HeaderBuilder\RemoveBuilder;
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Response,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
    Message\Environment\Environment,
    Message\Cookies\Cookies,
    Message\Query\Query,
    Message\Form\Form,
    Message\Files\Files
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\Map;
use Symfony\Component\HttpFoundation\Request;

class RemoveControllerTest extends ControllerTestCase
{
    public function testDefaultAction()
    {
        $this->buildContainer();
        $controller = $this->container->get(
            'innmind_rest_server.controller.resource.remove'
        );
        $called = false;
        $this
            ->container
            ->get('gateway.command.remove')
            ->method('__invoke')
            ->will($this->returnCallback(function($definition, $identity) use (&$called) {
                $called = true;
                $this->assertSame('foo', (string) $identity);
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
                        Url::fromString('/foo'),
                        new Method('DELETE'),
                        $protocol = new ProtocolVersion(1, 1),
                        new Headers,
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

        $this->assertTrue($called);
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
        new RemoveController(
            new Map('int', 'int'),
            $this->createMock(RemoveBuilder::class)
        );
    }
}
