<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Format;
use Innmind\Http\{
    Message\ResponseInterface,
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    Headers,
    Header\HeaderInterface,
    Header\ContentType,
    Header\ContentTypeValue,
    Header\ParameterInterface
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\Map;
use Symfony\Component\{
    HttpFoundation\Request,
    Serializer\SerializerInterface
};

final class OptionsController
{
    private $format;
    private $serializer;

    public function __construct(
        Format $format,
        SerializerInterface $serializer
    ) {
        $this->format = $format;
        $this->serializer = $serializer;
    }

    public function defaultAction(Request $request): ResponseInterface
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
