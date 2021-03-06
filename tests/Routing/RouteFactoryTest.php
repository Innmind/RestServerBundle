<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Routing;

use Innmind\Rest\ServerBundle\Routing\RouteFactory;
use Innmind\Rest\Server\Action;
use Innmind\Http\Message\Method;
use Symfony\Component\Routing\Route;
use PHPUnit\Framework\TestCase;

class RouteFactoryTest extends TestCase
{
    public function testMakeName()
    {
        $factory = new RouteFactory;

        $name = $factory->makeName('top_dir.sub_dir.res', Action::list());

        $this->assertSame(
            'innmind_rest_server.top_dir.sub_dir.res.list',
            $name
        );
    }

    public function testMakeRouteList()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', Action::list());

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/', $route->getPath());
        $this->assertSame([Method::GET], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'list',
                '_controller' => 'innmind_rest_server.controller.resource.list:defaultAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteGet()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', Action::get());

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([Method::GET], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'get',
                '_controller' => 'innmind_rest_server.controller.resource.get:defaultAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteCreate()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', Action::create());

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/', $route->getPath());
        $this->assertSame([Method::POST], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'create',
                '_controller' => 'innmind_rest_server.controller.resource.create:defaultAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteUpdate()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', Action::update());

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([Method::PUT], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'update',
                '_controller' => 'innmind_rest_server.controller.resource.update:defaultAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteRemove()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', Action::remove());

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([Method::DELETE], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'remove',
                '_controller' => 'innmind_rest_server.controller.resource.remove:defaultAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteLink()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', Action::link());

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([Method::LINK], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'link',
                '_controller' => 'innmind_rest_server.controller.resource.link:defaultAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteUnlink()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', Action::unlink());

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([Method::UNLINK], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'unlink',
                '_controller' => 'innmind_rest_server.controller.resource.unlink:defaultAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteOptions()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', Action::options());

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/', $route->getPath());
        $this->assertSame([Method::OPTIONS], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'options',
                '_controller' => 'innmind_rest_server.controller.resource.options:defaultAction',
            ],
            $route->getDefaults()
        );
    }
}
