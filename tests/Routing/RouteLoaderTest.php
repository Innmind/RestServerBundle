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
use Innmind\Http\Message\MethodInterface;
use Innmind\Immutable\Set;
use Symfony\Component\Routing\RouteCollection;

class RouteLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $loader;

    public function setUp()
    {
        $this->loader = new RouteLoader(
            (new YamlLoader(new Types))->load(
                (new Set('string'))->add(
                    'vendor/innmind/rest-server/fixtures/mapping.yml'
                )
            ),
            new RouteFactory
        );
    }

    public function testLoad()
    {
        $routes = $this->loader->load('.');

        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertSame(14, $routes->count());
        $this->assertSame(
            [
                'innmind_rest_server.top_dir.image.list',
                'innmind_rest_server.top_dir.image.get',
                'innmind_rest_server.top_dir.image.create',
                'innmind_rest_server.top_dir.image.update',
                'innmind_rest_server.top_dir.image.remove',
                'innmind_rest_server.top_dir.image.link',
                'innmind_rest_server.top_dir.image.unlink',
                'innmind_rest_server.top_dir.sub_dir.res.list',
                'innmind_rest_server.top_dir.sub_dir.res.get',
                'innmind_rest_server.top_dir.sub_dir.res.create',
                'innmind_rest_server.top_dir.sub_dir.res.update',
                'innmind_rest_server.top_dir.sub_dir.res.remove',
                'innmind_rest_server.top_dir.sub_dir.res.link',
                'innmind_rest_server.top_dir.sub_dir.res.unlink',
            ],
            array_keys($routes->all())
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.list');
        $this->assertSame('/top_dir/sub_dir/res/', $route->getPath());
        $this->assertSame([MethodInterface::GET], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => Action::LIST,
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.get');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::GET], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => Action::GET,
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.create');
        $this->assertSame('/top_dir/sub_dir/res/', $route->getPath());
        $this->assertSame([MethodInterface::POST], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => Action::CREATE,
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.update');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::PUT], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => Action::UPDATE,
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.remove');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::DELETE], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => Action::REMOVE,
            ],
            $route->getDefaults()
        );
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.link');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::LINK], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => Action::LINK,
            ],
            $route->getDefaults()
        );
        $this->assertSame('request.headers.has(\'Link\')', $route->getCondition());
        $route = $routes->get('innmind_rest_server.top_dir.sub_dir.res.unlink');
        $this->assertSame('/top_dir/sub_dir/res/{identity}', $route->getPath());
        $this->assertSame([MethodInterface::UNLINK], $route->getMethods());
        $this->assertSame(
            [
                '_innmind_resource' => 'top_dir.sub_dir.res',
                '_innmind_action' => Action::UNLINK,
            ],
            $route->getDefaults()
        );
        $this->assertSame('request.headers.has(\'Link\')', $route->getCondition());
    }

    public function testSupports()
    {
        $this->assertTrue($this->loader->supports('.', 'innmind_rest'));
        $this->assertFalse($this->loader->supports('.'));
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\RouteLoaderLoadedMultipleTimesException
     */
    public function testThrowWhenLoadedMultipleTimes()
    {
        $this->loader->load('.');
        $this->loader->load('.');
    }
}