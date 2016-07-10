<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Format;
use Innmind\Rest\Server\{
    Response\HeaderBuilder\CreateBuilderInterface,
    HttpResource,
    Definition\Access
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

final class CreateController
{
    private $gateways;
    private $serializer;
    private $format;
    private $headerBuilder;

    public function __construct(
        MapInterface $gateways,
        SerializerInterface $serializer,
        Format $format,
        CreateBuilderInterface $headerBuilder
    ) {
        $this->gateways = $gateways;
        $this->serializer = $serializer;
        $this->format = $format;
        $this->headerBuilder = $headerBuilder;
    }

    public function defaultAction(Request $request): ResponseInterface
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
                    'mask' => new Access(
                        (new Set('string'))->add(Access::CREATE)
                    ),
                ]
            )
        );

        return new Response(
            $code = new StatusCode(StatusCode::codes()->get('CREATED')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                $this->headerBuilder->build(
                    $identity,
                    $request,
                    $definition,
                    $resource
                )
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
