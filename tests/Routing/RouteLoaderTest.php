<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Routing;

use Innmind\Rest\ServerBundle\Routing\{
    RouteLoader,
    RouteFactory
};
use Innmind\Rest\Server\{
    Definition\Types,
    Definition\Loader\YamlLoader,
    Action
};
use Innmind\Http\Message\Method;
use Innmind\Immutable\{
    Set,
    Map
};
use Symfony\Component\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;

class RouteLoaderTest extends TestCase
{
    private $loader;

    public function setUp()
    {
        $this->loader = new RouteLoader(
            (new YamlLoader(new Types))->load(
                (new Set('string'))->add(
                    'fixtures/FixtureBundle/Resources/config/rest.yml'
                )
            ),
            new RouteFactory
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Definition\Directory>
     */
    public function testThrowWhenInvalidDirectoryMap()
    {
        new RouteLoader(
            new Map('string', 'string'),
            new RouteFactory
        );
    }

    public function testLoad()
    {
        $routes = $this->loader->load('.');

        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertSame(11, $routes->count());
        $this->assertSame(
            [
                'innmind_rest_server.top_dir.image.create',
                'innmind_rest_server.top_dir.image.options',
                'innmind_rest_server.top_dir.sub_dir.res.list',
                'innmind_rest_server.top_dir.sub_dir.res.get',
                'innmind_rest_server.top_dir.sub_dir.res.create',
                'innmind_rest_server.top_dir.sub_dir.res.update',
                'innmind_rest_server.top_dir.sub_dir.res.remove',
                'innmind_rest_server.top_dir.sub_dir.res.link',
                'innmind_rest_server.top_dir.sub_dir.res.unlink',
                'innmind_rest_server.top_dir.sub_dir.res.options',
                'innmind_rest_server_capabilities',
            ],
            array_keys($routes->all())
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.list');
        $this->assertSame('/top_dir/sub_dir/res/', $route->getPath());
        $this->assertSame([Method::GET], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => (string) Action::list(),
                '_controller' => 'innmind_rest_server.controller.resource.list:defaultAction',
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.get');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([Method::GET], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => (string) Action::get(),
                '_controller' => 'innmind_rest_server.controller.resource.get:defaultAction',
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.create');
        $this->assertSame('/top_dir/sub_dir/res/', $route->getPath());
        $this->assertSame([Method::POST], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => (string) Action::create(),
                '_controller' => 'innmind_rest_server.controller.resource.create:defaultAction',
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.update');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([Method::PUT], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => (string) Action::update(),
                '_controller' => 'innmind_rest_server.controller.resource.update:defaultAction',
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.remove');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([Method::DELETE], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => (string) Action::remove(),
                '_controller' => 'innmind_rest_server.controller.resource.remove:defaultAction',
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.link');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([Method::LINK], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => (string) Action::link(),
                '_controller' => 'innmind_rest_server.controller.resource.link:defaultAction',
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.unlink');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([Method::UNLINK], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => (string) Action::unlink(),
                '_controller' => 'innmind_rest_server.controller.resource.unlink:defaultAction',
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.options');
        $this->assertSame('/top_dir/sub_dir/res/', $route->getPath());
        $this->assertSame([Method::OPTIONS], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => (string) Action::options(),
                '_controller' => 'innmind_rest_server.controller.resource.options:defaultAction',
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server_capabilities');
        $this->assertSame('/*', $route->getPath());
        $this->assertSame([Method::OPTIONS], $route->getMethods());
        $this->assertSame(
            ['_controller' => 'innmind_rest_server.controller.capabilities:capabilitiesAction'],
            $route->getDefaults()
        );
    }

    public function testSupports()
    {
        $this->assertTrue($this->loader->supports('.', 'innmind_rest'));
        $this->assertFalse($this->loader->supports('.'));
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\RouteLoaderLoadedMultipleTimes
     */
    public function testThrowWhenLoadedMultipleTimes()
    {
        $this->loader->load('.');
        $this->loader->load('.');
    }
}
