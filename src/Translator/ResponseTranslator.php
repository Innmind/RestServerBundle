<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Translator;

use Innmind\Http\{
    Message\ResponseInterface,
    HeadersInterface,
    Header\HeaderValueInterface
};
use Innmind\Immutable\Map;
use Symfony\Component\HttpFoundation\Response;

final class ResponseTranslator
{
    private $transformed;

    public function __construct()
    {
        $this->transformed = new Map(
            ResponseInterface::class,
            Response::class
        );
    }

    public function translate(ResponseInterface $response): Response
    {
        if ($this->transformed->contains($response)) {
            return $this->transformed->get($response);
        }

        $sfResponse = (new Response(
            (string) $response->body(),
            $response->statusCode()->value(),
            $this->translateHeaders($response->headers())
        ))
            ->setProtocolVersion((string) $response->protocolVersion());
        $this->transformed = $this->transformed->put(
            $response,
            $sfResponse
        );

        return $sfResponse;
    }

    private function translateHeaders(HeadersInterface $headers): array
    {
        $raw = [];

        foreach ($headers as $header) {
            $raw[$header->name()] = $header
                ->values()
                ->reduce(
                    [],
                    function(array $carry, HeaderValueInterface $value) {
                        $carry[] = (string) $value;

                        return $carry;
                    }
                );
        }

        return $raw;
    }
}
