<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\RangeExtractor\{
    DelegationExtractor,
    ExtractorInterface
};
use Innmind\Immutable\Set;

final class RangeExtractorFactory
{
    public function make(array $extractors): DelegationExtractor
    {
        $set = new Set(ExtractorInterface::class);

        foreach ($extractors as $extractor) {
            $set = $set->add($extractor);
        }

        return new DelegationExtractor($set);
    }
}
