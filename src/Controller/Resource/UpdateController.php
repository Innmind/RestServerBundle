<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Format;
use Innmind\Rest\Server\{
    Gateway,
    Identity\Identity,
    HttpResource\HttpResource,
    Definition\Access,
    Response\HeaderBuilder\UpdateBuilder
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

final class UpdateController
{
    private $gateways;
    private $serializer;
    private $format;
    private $buildHeader;

    public function __construct(
        MapInterface $gateways,
        SerializerInterface $serializer,
        Format $format,
        UpdateBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type MapInterface<string, %s>',
                Gateway::class
            ));
        }

        $this->gateways = $gateways;
        $this->serializer = $serializer;
        $this->format = $format;
        $this->buildHeader = $headerBuilder;
    }

    public function defaultAction(Request $request, $identity): Response
    {
        $definition = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');

        $updater = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceUpdater();
        $updater(
            $definition,
            $identity = new Identity($identity),
            $resource = $this->serializer->deserialize(
                $request,
                HttpResource::class,
                'request_'.$this->format->contentType($request)->name(),
                [
                    'definition' => $definition,
                    'mask' => new Access(Access::UPDATE),
                ]
            )
        );

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                ($this->buildHeader)($request, $definition, $identity, $resource)
            ),
            new StringStream('')
        );
    }
}
