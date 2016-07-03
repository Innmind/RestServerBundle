<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Routing;

use Innmind\Rest\ServerBundle\Routing\RouteFactory;
use Innmind\Rest\Server\Action;
use Innmind\Http\Message\MethodInterface;
use Symfony\Component\Routing\Route;

class RouteFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeName()
    {
        $factory = new RouteFactory;

        $name = $factory->makeName('top_dir.sub_dir.res', new Action('list'));

        $this->assertSame(
            'innmind_rest_server.top_dir.sub_dir.res.list',
            $name
        );
    }

    public function testMakeRouteList()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', new Action('list'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/', $route->getPath());
        $this->assertSame([MethodInterface::GET], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'list',
                '_controller' => 'innmind_rest_server.controller.resource:listAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteGet()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', new Action('get'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::GET], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'get',
                '_controller' => 'innmind_rest_server.controller.resource:getAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteCreate()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', new Action('create'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/', $route->getPath());
        $this->assertSame([MethodInterface::POST], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'create',
                '_controller' => 'innmind_rest_server.controller.resource:createAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteUpdate()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', new Action('update'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::PUT], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'update',
                '_controller' => 'innmind_rest_server.controller.resource:updateAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteRemove()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', new Action('remove'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::DELETE], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'remove',
                '_controller' => 'innmind_rest_server.controller.resource:removeAction',
            ],
            $route->getDefaults()
        );
    }

    public function testMakeRouteLink()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', new Action('link'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::LINK], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'link',
                '_controller' => 'innmind_rest_server.controller.resource:linkAction',
            ],
            $route->getDefaults()
        );
        $this->assertSame(
            'request.headers.has(\'Link\')',
            $route->getCondition()
        );
    }

    public function testMakeRouteUnlink()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', new Action('unlink'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::UNLINK], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'unlink',
                '_controller' => 'innmind_rest_server.controller.resource:unlinkAction',
            ],
            $route->getDefaults()
        );
        $this->assertSame(
            'request.headers.has(\'Link\')',
            $route->getCondition()
        );
    }

    public function testMakeRouteOptions()
    {
        $factory = new RouteFactory;

        $route = $factory->makeRoute('image', new Action('options'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/image/', $route->getPath());
        $this->assertSame([MethodInterface::OPTIONS], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'image',
                '_innmind_action' => 'options',
                '_controller' => 'innmind_rest_server.controller.resource:optionsAction',
            ],
            $route->getDefaults()
        );
    }
}
