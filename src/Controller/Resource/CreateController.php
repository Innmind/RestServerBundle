<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\{
    Format,
    Exception\InvalidArgumentException
};
use Innmind\Rest\Server\{
    Response\HeaderBuilder\CreateBuilder,
    HttpResource\HttpResource,
    Definition\Access,
    Gateway
};
use Innmind\Http\{
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers
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

final class CreateController
{
    private $gateways;
    private $serializer;
    private $format;
    private $buildHeader;

    public function __construct(
        MapInterface $gateways,
        SerializerInterface $serializer,
        Format $format,
        CreateBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->gateways = $gateways;
        $this->serializer = $serializer;
        $this->format = $format;
        $this->buildHeader = $headerBuilder;
    }

    public function defaultAction(Request $request): Response
    {
        $definition = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');

        $creator = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceCreator();
        $identity = $creator(
            $definition,
            $resource = $this->serializer->deserialize(
                $request,
                HttpResource::class,
                'request_'.$this->format->contentType($request)->name(),
                [
                    'definition' => $definition,
                    'mask' => new Access(Access::CREATE),
                ]
            )
        );

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('CREATED')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                ($this->buildHeader)($identity, $request, $definition, $resource)
            ),
            new StringStream(
                $this->serializer->serialize(
                    $identity,
                    $this->format->acceptable($request)->name(),
                    [
                        'request' => $request,
                        'definition' => $definition,
                    ]
                )
            )
        );
    }
}
