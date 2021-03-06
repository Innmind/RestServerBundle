<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\Factory\GatewaysFactory;
use Innmind\Rest\Server\Gateway;
use Innmind\Immutable\MapInterface;
use PHPUnit\Framework\TestCase;

class GatewaysFactoryTest extends TestCase
{
    public function testMake()
    {
        $factory = new GatewaysFactory;

        $gateways = $factory->make([
            'command' => $gateway = $this->createMock(Gateway::class),
        ]);

        $this->assertInstanceOf(MapInterface::class, $gateways);
        $this->assertSame('string', (string) $gateways->keyType());
        $this->assertSame(Gateway::class, (string) $gateways->valueType());
        $this->assertSame('command', $gateways->key());
        $this->assertSame($gateway, $gateways->current());
    }
}
