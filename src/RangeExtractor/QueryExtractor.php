<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\RangeExtractor;

use Innmind\Rest\ServerBundle\Exception\RangeNotFoundException;
use Innmind\Rest\Server\Request\Range;
use Innmind\Http\Message\ServerRequestInterface;

final class QueryExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(ServerRequestInterface $request): Range
    {
        if (
            !$request->query()->has('range') ||
            !is_array($request->query()->get('range')->value()) ||
            count($request->query()->get('range')->value()) !== 2
        ) {
            throw new RangeNotFoundException;
        }

        return new Range(
            $request
                ->query()
                ->get('range')
                ->value()[0],
            $request
                ->query()
                ->get('range')
                ->value()[1]
        );
    }
}
