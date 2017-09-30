<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Routing;

use Innmind\Rest\Server\Action;
use Innmind\Http\Message\Method;
use Innmind\Immutable\Str;
use Symfony\Component\Routing\Route;

final class RouteFactory
{
    /**
     * Make a route name
     *
     * @param string $path Path to resource definition
     * @param Action $action
     *
     * @return string
     */
    public function makeName(string $path, Action $action): string
    {
        return (string) (new Str($path))
            ->prepend('innmind_rest_server.')
            ->append('.')
            ->append((string) $action);
    }

    /**
     * Make a route
     *
     * @param string $path Path to resource definition
     * @param Action $action
     *
     * @return Route
     */
    public function makeRoute(string $path, Action $action): Route
    {
        $name = $path;
        $path = (new Str($path))->replace('.', '/')->append('/');

        switch ($action) {
            case Action::get():
                $path = $path->append('{identity}');
            case Action::list():
                $method = Method::GET;
                break;
            case Action::create():
                $method = Method::POST;
                break;
            case Action::update():
                $method = Method::PUT;
                $path = $path->append('{identity}');
                break;
            case Action::remove():
                $method = Method::DELETE;
                $path = $path->append('{identity}');
                break;
            case Action::link():
                $method = Method::LINK;
                $path = $path->append('{identity}');
                break;
            case Action::unlink():
                $method = Method::UNLINK;
                $path = $path->append('{identity}');
                break;
            case Action::options():
                $method = Method::OPTIONS;
                break;
        }

        return new Route(
            (string) $path,
            [
                '_innmind_resource' => $name,
                '_innmind_action' => (string) $action,
                '_controller' => sprintf(
                    'innmind_rest_server.controller.resource.%s:defaultAction',
                    $action
                ),
            ],
            [],
            [],
            '',
            [],
            $method
        );
    }
}
