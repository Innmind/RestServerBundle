<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Controller;

use Innmind\Rest\ServerBundle\{
    Controller\CapabilitiesController,
    Routing\RouteFactory
};
use Innmind\Rest\Server\Definition\{
    Types,
    Loader\YamlLoader
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
    Header\HeaderInterface
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    Map,
    Set
};
use Symfony\Component\{
    Routing\Generator\UrlGeneratorInterface,
    HttpFoundation\Request
};

class CapabilitiesControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidDirectoryMap()
    {
        new CapabilitiesController(
            new Map('int', 'int'),
            new RouteFactory,
            $this->createMock(UrlGeneratorInterface::class)
        );
    }

    public function testCapabilitiesAction()
    {
        $controller = new CapabilitiesController(
            (new YamlLoader(new Types))->load(
                (new Set('string'))->add(
                    'vendor/innmind/rest-server/fixtures/mapping.yml'
                )
            ),
            new RouteFactory,
            $generator = $this->createMock(UrlGeneratorInterface::class)
        );
        $generator
            ->method('generate')
            ->will($this->returnCallback(function(string $route) {
                $route = str_replace('innmind_rest_server', '', $route);
                $route = str_replace('options', '', $route);

                return str_replace('.', '/', $route);
            }));

        $request = new ServerRequest(
            Url::fromString('/'),
            new Method('GET'),
            $protocol = new ProtocolVersion(1, 1),
            new Headers(new Map('string', HeaderInterface::class)),
            new StringStream(''),
            new Environment(new Map('string', 'scalar')),
            new Cookies(new Map('string', 'scalar')),
            new Query(new Map('string', QueryParameterInterface::class)),
            new Form(new Map('scalar', FormParameterInterface::class)),
            new Files(new Map('string', FileInterface::class))
        );
        $sfRequest = new Request;
        $sfRequest->attributes->set('_innmind_request', $request);

        $response = $controller->capabilitiesAction($sfRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', (string) $response->reasonPhrase());
        $this->assertSame($protocol, $response->protocolVersion());
        $this->assertSame(1, $response->headers()->count());
        $this->assertSame(
            'Link : </top_dir/image/>; rel="top_dir.image", </top_dir/sub_dir/res/>; rel="top_dir.sub_dir.res"',
            (string) $response->headers()->get('Link')
        );
        $this->assertSame('', (string) $response->body());
    }
}
