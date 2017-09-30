<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\Server\Gateway;
use Innmind\Immutable\{
    Map,
    MapInterface
};

final class GatewaysFactory
{
    /**
     * @param Gateway[]
     *
     * @return MapInterface<string, Gateway>
     */
    public function make(array $gateways): MapInterface
    {
        $map = new Map('string', Gateway::class);

        foreach ($gateways as $name => $gateway) {
            $map = $map->put($name, $gateway);
        }

        return $map;
    }
}
