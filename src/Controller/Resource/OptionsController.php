<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Format;
use Innmind\Http\{
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
    Header,
    Header\ContentType,
    Header\ContentTypeValue
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

    public function defaultAction(Request $request): Response
    {
        $definition = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');
        $format = $this->format->acceptable($request);
        $mediaType = $format->preferredMediaType();

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('OK')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                (new Map('string', Header::class))
                    ->put(
                        'Content-Type',
                        new ContentType(
                            new ContentTypeValue(
                                $mediaType->topLevel(),
                                $mediaType->subType()
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
