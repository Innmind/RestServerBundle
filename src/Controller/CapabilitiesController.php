<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller;

use Innmind\Rest\ServerBundle\{
    Routing\RouteFactory,
    Exception\InvalidArgumentException
};
use Innmind\Rest\Server\{
    Definition\Directory,
    Action
};
use Innmind\Http\{
    Message\ResponseInterface,
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    Header\HeaderInterface,
    Header\LinkValue,
    Header\Link,
    Header\ParameterInterface,
    Header\HeaderValueInterface,
    Headers
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    Map,
    Set,
    MapInterface
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
            throw new InvalidArgumentException;
        }

        $this->directories = $directories;
        $this->routeFactory = $routeFactory;
        $this->generator = $generator;
    }

    public function capabilitiesAction(Request $request): ResponseInterface
    {
        $request = $request->attributes->get('_innmind_request');

        $links = $this
            ->directories
            ->reduce(
                [],
                function(array $carry, string $name, Directory $directory) {
                    return array_merge(
                        $carry,
                        $directory
                            ->flatten()
                            ->reduce(
                                [],
                                function(array $carry, string $name) {
                                    $carry[$name] = $this->generator->generate(
                                        $this
                                            ->routeFactory
                                            ->makeRoute(
                                                $name,
                                                new Action(Action::OPTIONS)
                                            )
                                            ->getPath()
                                    );

                                    return $carry;
                                }
                            )
                    );
                }
            );
        $set = new Set(HeaderValueInterface::class);

        foreach ($links as $name => $link) {
            $set = $set->add(new LinkValue(
                Url::fromString($link),
                $name,
                new Map('string', ParameterInterface::class)
            ));
        }

        return new Response(
            $code = new StatusCode(StatusCode::codes()->get('OK')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                (new Map('string', HeaderInterface::class))
                    ->put(
                        'Link',
                        new Link($set)
                    )
            ),
            new StringStream('')
        );
    }
}
