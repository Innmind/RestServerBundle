<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\RangeExtractor;

use Innmind\Rest\ServerBundle\Exception\RangeNotFoundException;
use Innmind\Rest\Server\Request\Range;
use Innmind\Http\Message\ServerRequestInterface;

final class HeaderExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(ServerRequestInterface $request): Range
    {
        if (!$request->headers()->has('Range')) {
            throw new RangeNotFoundException;
        }

        return new Range(
            $request
                ->headers()
                ->get('Range')
                ->values()
                ->current()
                ->firstPosition(),
            $request
                ->headers()
                ->get('Range')
                ->values()
                ->current()
                ->lastPosition()
        );
    }
}
