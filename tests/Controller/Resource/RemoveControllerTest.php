<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Controller\Resource\RemoveController;
use Innmind\Rest\Server\Response\HeaderBuilder\RemoveBuilderInterface;
use Innmind\Http\{
    Message\ServerRequest,
    Message\ResponseInterface,
    Message\Method,
    ProtocolVersion,
    Headers,
    Header\HeaderInterface,
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
                        new Headers(new Map('string', HeaderInterface::class)),
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

        $this->assertTrue($called);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', (string) $response->reasonPhrase());
        $this->assertCount(0, $response->headers());
        $this->assertSame('', (string) $response->body());
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidGatewayMap()
    {
        new RemoveController(
            new Map('int', 'int'),
            $this->createMock(RemoveBuilderInterface::class)
        );
    }
}
