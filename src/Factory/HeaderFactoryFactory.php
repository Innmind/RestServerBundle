<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Http\Factory\{
    Header\DefaultFactory,
    HeaderFactoryInterface
};
use Innmind\Immutable\Map;

final class HeaderFactoryFactory
{
    /**
     * @param array<string, HeaderFactoryInterface> $factories
     *
     * @return DefaultFactory
     */
    public function make(array $factories): DefaultFactory
    {
        $map = new Map('string', HeaderFactoryInterface::class);

        foreach ($factories as $alias => $factory) {
            $map = $map->put($alias, $factory);
        }

        return new DefaultFactory($map);
    }
}
