<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller;

use Innmind\Rest\ServerBundle\Routing\RouteFactory;
use Innmind\Rest\Server\{
    Definition\Directory,
    Action
};
use Innmind\Http\{
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Header,
    Header\LinkValue,
    Header\Link,
    Header\Value,
    Headers\Headers
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    Set,
    MapInterface,
    Map
};
use Symfony\Component\{
    Routing\Generator\UrlGeneratorInterface,
    HttpFoundation\Request
};

final class CapabilitiesController
{
    private $directories;
    private $routeFactory;
    private $generator;

    public function __construct(
        MapInterface $directories,
        RouteFactory $routeFactory,
        UrlGeneratorInterface $generator
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
        $this->generator = $generator;
    }

    public function capabilitiesAction(Request $request): Response
    {
        $request = $request->attributes->get('_innmind_request');

        $links = $this
            ->directories
            ->reduce(
                new Map('string', 'string'),
                function(Map $carry, string $name, Directory $directory): Map {
                    return $directory
                        ->flatten()
                        ->reduce(
                            $carry,
                            function(Map $carry, string $name): Map {
                                $route = $this->routeFactory->makeName(
                                    $name,
                                    Action::options()
                                );

                                return $carry->put(
                                    $name,
                                    $this->generator->generate($route)
                                );
                            }
                        );
                }
            )
            ->reduce(
                new Set(Value::class),
                static function(Set $carry, string $name, string $link): Set {
                    return $carry->add(new LinkValue(
                        Url::fromString($link),
                        $name
                    ));
                }
            );

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('OK')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                (new Map('string', Header::class))
                    ->put(
                        'Link',
                        new Link(...$links)
                    )
            ),
            new StringStream('')
        );
    }
}
