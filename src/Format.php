<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle;

use Innmind\Rest\Server\{
    Formats,
    Format\Format,
    Format\MediaType
};
use Innmind\Http\Message\ServerRequestInterface;
use Negotiation\Negotiator;

final class Format
{
    private $accept;
    private $contentType;
    private $negotiator;

    public function __construct(
        Formats $accept,
        Formats $contentType
    ) {
        $this->accept = $accept;
        $this->contentType = $contentType;
        $this->negotiator = new Negotiator;
    }

    public function acceptable(ServerRequestInterface $request): Format
    {
        $best = $this->negotiator->getBest(
            $request
                ->headers()
                ->get('Accept')
                ->values()
                ->join(', '),
            $this
                ->accept
                ->mediaTypes()
                ->reduce(
                    [],
                    function(array $carry, MediaType $type) {
                        $carry[] = (string) $type;
                        return $carry;
                    }
                )
        );

        return $this->best(
            $best->getBasePart().'/'.$best->getSubPart()
        );
    }

    public function contentType(ServerRequestInterface $request): Format
    {
        return $this->contentType->fromMediaType(
            (string) $request
                ->headers()
                ->get('Content-Type')
                ->values()
                ->current()
        );
    }

    private function best(string $mediaType): Format
    {
        if ($mediaType === '*/*') {
            return $this
                ->accept
                ->all()
                ->values()
                ->sort(function(Format $a, Format $b) {
                    return $a->priority() > $b->priority();
                })
                ->first();
        }

        return $this->accept->fromMediaType($mediaType);
    }
}
