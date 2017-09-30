<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\{
    Format,
    Exception\InvalidArgumentException
};
use Innmind\Rest\Server\{
    RangeExtractor\Extractor,
    SpecificationBuilder\Builder,
    Response\HeaderBuilder\ListBuilder,
    Gateway,
    Request\Range,
    Exception\RangeNotFound,
    Exception\NoFilterFound
};
use Innmind\Http\{
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
    Exception\Http\RangeNotSatisfiable
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\MapInterface;
use Symfony\Component\{
    HttpFoundation\Request,
    Serializer\SerializerInterface
};

final class ListController
{
    private $format;
    private $serializer;
    private $extractRange;
    private $buildSpecification;
    private $gateways;
    private $buildHeader;

    public function __construct(
        Format $format,
        SerializerInterface $serializer,
        Extractor $rangeExtractor,
        Builder $specificationBuilder,
        MapInterface $gateways,
        ListBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->format = $format;
        $this->serializer = $serializer;
        $this->extractRange = $rangeExtractor;
        $this->buildSpecification = $specificationBuilder;
        $this->gateways = $gateways;
        $this->buildHeader = $headerBuilder;
    }

    public function defaultAction(Request $request): Response
    {
        $definition = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');

        try {
            $range = ($this->extractRange)($request);
        } catch (RangeNotFound $e) {
            $range = null;
        }

        try {
            $specification = ($this->buildSpecification)($request, $definition);
        } catch (NoFilterFound $e) {
            $specification = null;
        }

        $accessor = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceListAccessor();
        $identities = $accessor(
            $definition,
            $specification,
            $range
        );

        if (
            $identities->size() === 0 &&
            $range instanceof Range
        ) {
            throw new RangeNotSatisfiable;
        }

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get(
                $range instanceof Range ? 'PARTIAL_CONTENT' : 'OK'
            )),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                ($this->buildHeader)(
                    $identities,
                    $request,
                    $definition,
                    $specification,
                    $range
                )
            ),
            new StringStream(
                $this->serializer->serialize(
                    $identities,
                    $this->format->acceptable($request)->name(),
                    [
                        'request' => $request,
                        'definition' => $definition,
                        'specification' => $specification,
                        'range' => $range,
                    ]
                )
            )
        );
    }
}
