<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\{
    ServerBundle\Factory\DirectoriesFactory,
    Server\Definition\Loader\YamlLoader,
    Server\Definition\Types,
    Server\Definition\Directory
};
use Innmind\Immutable\MapInterface;
use PHPUnit\Framework\TestCase;

class DirectoriesFactoryTest extends TestCase
{
    public function testMake()
    {
        $factory = new DirectoriesFactory(
            new YamlLoader(new Types)
        );

        $directories = $factory->make([
            'vendor/innmind/rest-server/fixtures/mapping.yml'
        ]);

        $this->assertInstanceOf(MapInterface::class, $directories);
        $this->assertSame('string', (string) $directories->keyType());
        $this->assertSame(Directory::class, (string) $directories->valueType());
        $this->assertSame(1, $directories->size());
    }
}
