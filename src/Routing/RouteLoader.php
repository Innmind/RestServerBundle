<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Routing;

use Innmind\Rest\ServerBundle\Exception\RouteLoaderLoadedMultipleTimes;
use Innmind\Rest\Server\{
    Definition\Directory,
    Definition\HttpResource,
    Action
};
use Innmind\Http\Message\Method;
use Innmind\Immutable\MapInterface;
use Symfony\Component\{
    Config\Loader\Loader,
    Routing\RouteCollection,
    Routing\Route
};

final class RouteLoader extends Loader
{
    private $directories;
    private $routeFactory;
    private $imported = false;

    public function __construct(
        MapInterface $directories,
        RouteFactory $routeFactory
    ) {
        if (
            (string) $directories->keyType() !== 'string' ||
            (string) $directories->valueType() !== Directory::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type MapInterface<string, %s>',
                Directory::class
            ));
        }

        $this->directories = $directories;
        $this->routeFactory = $routeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if ($this->imported === true) {
            throw new RouteLoaderLoadedMultipleTimes;
        }

        $routes = new RouteCollection;

        foreach ($this->directories as $directory) {
            $routes->addCollection(
                $this->buildDirectoryRoutes($directory)
            );
        }

        $routes->add(
            'innmind_rest_server_capabilities',
            new Route(
                '/*',
                ['_controller' => 'innmind_rest_server.controller.capabilities:capabilitiesAction'],
                [],
                [],
                '',
                [],
                [Method::OPTIONS]
            )
        );

        $this->imported = true;

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return $type === 'innmind_rest';
    }

    private function buildDirectoryRoutes(Directory $directory): RouteCollection
    {
        return $directory
            ->flatten()
            ->reduce(
                new RouteCollection,
                function(RouteCollection $routes, string $name, HttpResource $definition) {
                    $routes->addCollection(
                        $this->buildResourceRoutes($name, $definition)
                    );

                    return $routes;
                }
            );
    }

    private function buildResourceRoutes(
        string $name,
        HttpResource $definition
    ): RouteCollection {
        return Action::all()
            ->reduce(
                new RouteCollection,
                function(RouteCollection $carry, Action $action) use ($name, $definition) {
                    if (
                        $definition->options()->contains('actions') &&
                        !in_array((string) $action, $definition->options()->get('actions'))
                    ) {
                        return $carry;
                    }

                    $carry->add(
                        $this->routeFactory->makeName($name, $action),
                        $this->routeFactory->makeRoute($name, $action)
                    );

                    return $carry;
                }
            );
    }
}
