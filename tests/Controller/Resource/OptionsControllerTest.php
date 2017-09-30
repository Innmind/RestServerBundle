<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller\Resource;

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
use Symfony\Component\HttpFoundation\Request;

class OptionsControllerTest extends ControllerTestCase
{
    private $controller;

    public function setUp()
    {
        $this->buildContainer();
        $this->controller = $this->container->get(
            'innmind_rest_server.controller.resource.options'
        );
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
            )
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', (string) $response->reasonPhrase());
        $this->assertSame($protocol, $response->protocolVersion());
        $this->assertSame(1, $response->headers()->count());
        $this->assertSame(
            'Content-Type : application/json',
            (string) $response->headers()->get('content-type')
        );
        $this->assertSame(
            json_encode([
                'identity' => 'uuid',
                'properties' => [
                    'uuid' => [
                        'type' => 'string',
                        'access' => ['READ'],
                        'variants' => [],
                        'optional' => false,
                    ],
                    'url' => [
                        'type' => 'string',
                        'access' => ['READ', 'CREATE', 'UPDATE'],
                        'variants' => [],
                        'optional' => false,
                    ],
                ],
                'metas' => [],
                'rangeable' => true,
                'linkable_to' => [
                    'rel' => 'top_dir.sub_dir.res',
                ],
            ]),
            (string) $response->body()
        );
    }
}
