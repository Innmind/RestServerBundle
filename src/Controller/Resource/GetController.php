<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\{
    Format,
    Exception\InvalidArgumentException
};
use Innmind\Rest\Server\{
    Response\HeaderBuilder\GetBuilderInterface,
    GatewayInterface,
    Identity
};
use Innmind\Http\{
    Message\ResponseInterface,
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    Headers
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\MapInterface;
use Symfony\Component\{
    HttpFoundation\Request,
    Serializer\SerializerInterface
};

final class GetController
{
    private $format;
    private $serializer;
    private $gateways;
    private $headerBuilder;

    public function __construct(
        Format $format,
        SerializerInterface $serializer,
        MapInterface $gateways,
        GetBuilderInterface $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== GatewayInterface::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->format = $format;
        $this->serializer = $serializer;
        $this->gateways = $gateways;
        $this->headerBuilder = $headerBuilder;
    }

    public function defaultAction(Request $request, $identity): ResponseInterface
    {
        $definition = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');

        $accessor = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceAccessor();
        $resource = $accessor(
            $definition,
            $identity = new Identity($identity)
        );

        return new Response(
            $code = new StatusCode(StatusCode::codes()->get('OK')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                $this->headerBuilder->build(
                    $resource,
                    $request,
                    $definition,
                    $identity
                )
            ),
            new StringStream(
                $this->serializer->serialize(
                    $resource,
                    $this->format->acceptable($request)->name(),
                    [
                        'request' => $request,
                        'definition' => $definition,
                        'identity' => $identity,
                    ]
                )
            )
        );
    }
}
