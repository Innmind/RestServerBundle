<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\{
    Format,
    Exception\InvalidArgumentException
};
use Innmind\Rest\Server\{
    RangeExtractor\ExtractorInterface,
    SpecificationBuilder\BuilderInterface,
    Response\HeaderBuilder\ListBuilderInterface,
    GatewayInterface,
    Request\Range,
    Exception\RangeNotFoundException,
    Exception\NoFilterFoundException
};
use Innmind\Http\{
    Message\ResponseInterface,
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    Headers,
    Exception\Http\RangeNotSatisfiableException
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
    private $rangeExtractor;
    private $specificationBuilder;
    private $gateways;
    private $headerBuilder;

    public function __construct(
        Format $format,
        SerializerInterface $serializer,
        ExtractorInterface $rangeExtractor,
        BuilderInterface $specificationBuilder,
        MapInterface $gateways,
        ListBuilderInterface $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== GatewayInterface::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->format = $format;
        $this->serializer = $serializer;
        $this->rangeExtractor = $rangeExtractor;
        $this->specificationBuilder = $specificationBuilder;
        $this->gateways = $gateways;
        $this->headerBuilder = $headerBuilder;
    }

    public function defaultAction(Request $request): ResponseInterface
    {
        $definition = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');

        try {
            $range = $this->rangeExtractor->extract($request);
        } catch (RangeNotFoundException $e) {
            $range = null;
        }

        try {
            $specification = $this->specificationBuilder->buildFrom(
                $request,
                $definition
            );
        } catch (NoFilterFoundException $e) {
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
            throw new RangeNotSatisfiableException;
        }

        return new Response(
            $code = new StatusCode(StatusCode::codes()->get(
                $range instanceof Range ? 'PARTIAL_CONTENT' : 'OK'
            )),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                $this->headerBuilder->build(
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
