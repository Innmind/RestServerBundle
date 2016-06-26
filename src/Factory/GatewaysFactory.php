<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\Server\GatewayInterface;
use Innmind\Immutable\{
    Map,
    MapInterface
};

final class GatewaysFactory
{
    /**
     * @param GatewayInterface[]
     *
     * @return MapInterface<string, GatewayInterface>
     */
    public function make(array $gateways): MapInterface
    {
        $map = new Map('string', GatewayInterface::class);

        foreach ($gateways as $name => $gateway) {
            $map = $map->put($name, $gateway);
        }

        return $map;
    }
}
