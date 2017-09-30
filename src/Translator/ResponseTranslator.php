<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Translator;

use Innmind\Http\{
    Message\Response,
    Headers,
    Header\Value
};
use Innmind\Immutable\Map;
use Symfony\Component\HttpFoundation\Response as SfResponse;

final class ResponseTranslator
{
    private $transformed;

    public function __construct()
    {
        $this->transformed = new Map(
            Response::class,
            SfResponse::class
        );
    }

    public function translate(Response $response): SfResponse
    {
        if ($this->transformed->contains($response)) {
            return $this->transformed->get($response);
        }

        $sfResponse = (new SfResponse(
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

    private function translateHeaders(Headers $headers): array
    {
        $raw = [];

        foreach ($headers as $header) {
            $raw[$header->name()] = $header
                ->values()
                ->reduce(
                    [],
                    function(array $carry, Value $value) {
                        $carry[] = (string) $value;

                        return $carry;
                    }
                );
        }

        return $raw;
    }
}
