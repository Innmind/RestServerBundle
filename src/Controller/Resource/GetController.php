<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Format;
use Innmind\Rest\Server\{
    Response\HeaderBuilder\GetBuilder,
    Gateway,
    Identity\Identity
};
use Innmind\Http\{
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers
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
    private $buildHeader;

    public function __construct(
        Format $format,
        SerializerInterface $serializer,
        MapInterface $gateways,
        GetBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 3 must be of type MapInterface<string, %s>',
                Gateway::class
            ));
        }

        $this->format = $format;
        $this->serializer = $serializer;
        $this->gateways = $gateways;
        $this->buildHeader = $headerBuilder;
    }

    public function defaultAction(Request $request, $identity): Response
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

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('OK')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                ($this->buildHeader)($resource, $request, $definition, $identity)
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
