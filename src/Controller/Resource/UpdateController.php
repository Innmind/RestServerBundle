<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\{
    Format,
    Exception\InvalidArgumentException
};
use Innmind\Rest\Server\{
    GatewayInterface,
    Identity,
    HttpResource,
    Definition\Access,
    Response\HeaderBuilder\UpdateBuilderInterface
};
use Innmind\Http\{
    Message\ResponseInterface,
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    Headers
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    MapInterface,
    Set
};
use Symfony\Component\{
    Serializer\SerializerInterface,
    HttpFoundation\Request
};

final class UpdateController
{
    private $gateways;
    private $serializer;
    private $format;
    private $headerBuilder;

    public function __construct(
        MapInterface $gateways,
        SerializerInterface $serializer,
        Format $format,
        UpdateBuilderInterface $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== GatewayInterface::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->gateways = $gateways;
        $this->serializer = $serializer;
        $this->format = $format;
        $this->headerBuilder = $headerBuilder;
    }

    public function defaultAction(Request $request, $identity): ResponseInterface
    {
        $definition = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');

        $accessor = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceUpdater();
        $accessor(
            $definition,
            $identity = new Identity($identity),
            $resource = $this->serializer->deserialize(
                $request,
                HttpResource::class,
                'request_'.$this->format->contentType($request)->name(),
                [
                    'definition' => $definition,
                    'mask' => new Access(
                        (new Set('string'))->add(Access::UPDATE)
                    ),
                ]
            )
        );

        return new Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                $this->headerBuilder->build(
                    $request,
                    $definition,
                    $identity,
                    $resource
                )
            ),
            new StringStream('')
        );
    }
}
