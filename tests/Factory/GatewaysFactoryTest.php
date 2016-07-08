<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\Factory\GatewaysFactory;
use Innmind\Rest\Server\GatewayInterface;
use Innmind\Immutable\MapInterface;

class GatewaysFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $factory = new GatewaysFactory;

        $gateways = $factory->make([
            'command' => $gateway = $this->createMock(GatewayInterface::class),
        ]);

        $this->assertInstanceOf(MapInterface::class, $gateways);
        $this->assertSame('string', (string) $gateways->keyType());
        $this->assertSame(GatewayInterface::class, (string) $gateways->valueType());
        $this->assertSame('command', $gateways->key());
        $this->assertSame($gateway, $gateways->current());
    }
}
