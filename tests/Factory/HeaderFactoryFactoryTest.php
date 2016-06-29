<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\Factory\HeaderFactoryFactory;
use Innmind\Http\{
    Factory\Header\RangeFactory,
    Factory\Header\DefaultFactory,
    Header\Range
};
use Innmind\Immutable\StringPrimitive as Str;

class HeaderFactoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $factory = new HeaderFactoryFactory;

        $factory = $factory->make([
            'range' => new RangeFactory,
        ]);

        $this->assertInstanceOf(DefaultFactory::class, $factory);
        $this->assertInstanceOf(
            Range::class,
            $factory->make(
                new Str('Range'),
                new Str('resources=0-42')
            )
        );
    }
}
