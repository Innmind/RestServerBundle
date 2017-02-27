<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Routing;

use Innmind\Rest\Server\Action;
use Innmind\Http\Message\MethodInterface;
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

        switch ((string) $action) {
            case Action::GET:
                $path = $path->append('{identity}');
            case Action::LIST:
                $method = MethodInterface::GET;
                break;
            case Action::CREATE:
                $method = MethodInterface::POST;
                break;
            case Action::UPDATE:
                $method = MethodInterface::PUT;
                $path = $path->append('{identity}');
                break;
            case Action::REMOVE:
                $method = MethodInterface::DELETE;
                $path = $path->append('{identity}');
                break;
            case Action::LINK:
                $method = MethodInterface::LINK;
                $path = $path->append('{identity}');
                break;
            case Action::UNLINK:
                $method = MethodInterface::UNLINK;
                $path = $path->append('{identity}');
                break;
            case Action::OPTIONS:
                $method = MethodInterface::OPTIONS;
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
