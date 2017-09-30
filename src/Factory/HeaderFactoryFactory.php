<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Http\Factory\{
    Header\TryFactory,
    Header\DelegationFactory,
    Header\HeaderFactory,
    HeaderFactory as HeaderFactoryInterface
};
use Innmind\Immutable\Map;

final class HeaderFactoryFactory
{
    /**
     * @param array<string, HeaderFactoryInterface> $factories
     *
     * @return TryFactory
     */
    public function make(array $factories): TryFactory
    {
        $map = new Map('string', HeaderFactoryInterface::class);

        foreach ($factories as $alias => $factory) {
            $map = $map->put($alias, $factory);
        }

        return new TryFactory(
            new DelegationFactory($map),
            new HeaderFactory
        );
    }
}
