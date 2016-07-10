<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller;

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
    Header\HeaderInterface,
    Header\ContentType,
    Header\ContentTypeValue,
    Header\ParameterInterface,
    Exception\Http\RangeNotSatisfiableException
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    Map,
    MapInterface
};
use Symfony\Component\{
    HttpFoundation\Request,
    Serializer\SerializerInterface
};

final class ResourceController
{
    private $format;
    private $serializer;
    private $rangeExtractor;
    private $specificationBuilder;
    private $gateways;
    private $listHeaderBuilder;

    public function __construct(
        Format $format,
        SerializerInterface $serializer,
        ExtractorInterface $rangeExtractor,
        BuilderInterface $specificationBuilder,
        MapInterface $gateways,
        ListBuilderInterface $listHeaderBuilder
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
        $this->listHeaderBuilder = $listHeaderBuilder;
    }

    public function listAction(Request $request): ResponseInterface
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
                $this->listHeaderBuilder->build(
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

    public function optionsAction(Request $request): ResponseInterface
    {
        $definition = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');
        $format = $this->format->acceptable($request);
        $mediaType = $format->preferredMediaType();

        return new Response(
            $code = new StatusCode(StatusCode::codes()->get('OK')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                (new Map('string', HeaderInterface::class))
                    ->put(
                        'Content-Type',
                        new ContentType(
                            new ContentTypeValue(
                                $mediaType->topLevel(),
                                $mediaType->subType(),
                                new Map('string', ParameterInterface::class)
                            )
                        )
                    )
            ),
            new StringStream(
                $this->serializer->serialize(
                    $definition,
                    $format->name()
                )
            )
        );
    }
}
