<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Exception\InvalidArgumentException;
use Innmind\Rest\Server\{
    Response\HeaderBuilder\UnlinkBuilderInterface,
    Definition\Locator,
    GatewayInterface,
    Reference,
    Identity,
    Link\ParameterInterface,
    Link\Parameter
};
use Innmind\Http\{
    Message\ResponseInterface,
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    Headers,
    Header\Link,
    Header\LinkValue,
    Header\ParameterInterface as LinkParameterInterface
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    MapInterface,
    Map
};
use Symfony\Component\{
    Routing\RouterInterface,
    HttpFoundation\Request
};

final class UnlinkController
{
    private $gateways;
    private $headerBuilder;
    private $router;
    private $locator;

    public function __construct(
        MapInterface $gateways,
        UnlinkBuilderInterface $headerBuilder,
        RouterInterface $router,
        Locator $locator
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== GatewayInterface::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->gateways = $gateways;
        $this->headerBuilder = $headerBuilder;
        $this->router = $router;
        $this->locator = $locator;
    }

    public function defaultAction(Request $request, $identity): ResponseInterface
    {
        $from = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');
        $tos = $this->extractReferences($request->headers()->get('Link'));

        $linker = $this
            ->gateways
            ->get((string) $from->gateway())
            ->resourceLinker();
        $linker(
            $from = new Reference($from, new Identity($identity)),
            $tos
        );

        return new Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                $this->headerBuilder->build(
                    $request,
                    $from,
                    $tos
                )
            ),
            new StringStream('')
        );
    }

    /**
     * @return MapInterface<Reference, MapInterface<string, ParameterInterface>>
     */
    private function extractReferences(Link $header): MapInterface
    {
        return $header
            ->values()
            ->reduce(
                new Map(Reference::class, MapInterface::class),
                function(Map $carry, LinkValue $value): Map
                {
                    $infos = $this->router->match((string) $value->url());

                    return $carry->put(
                        new Reference(
                            $this->locator->locate($infos['_innmind_resource']),
                            new Identity($infos['identity'])
                        ),
                        $value
                            ->parameters()
                            ->reduce(
                                new Map('string', ParameterInterface::class),
                                function(Map $carry, string $name, LinkParameterInterface $param): Map {
                                    return $carry->put(
                                        $name,
                                        new Parameter($name, $param->value())
                                    );
                                }
                            )
                            ->put(
                                'rel',
                                new Parameter('rel', $value->relationship())
                            )
                    );
                }
            );
    }
}
