<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\Factory\FormatsFactory;
use Innmind\Rest\Server\{
    Formats,
    Format\MediaType
};

class FormatsFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $factory = new FormatsFactory;

        $formats = $factory->make([
            'json' => [
                'priority' => 42,
                'media_types' => [
                    'application/json' => 0,
                ],
            ],
        ]);

        $this->assertInstanceOf(Formats::class, $formats);
        $this->assertSame('json', $formats->get('json')->name());
        $this->assertSame(42, $formats->get('json')->priority());
        $this->assertSame(
            ['application/json' => 0],
            $formats
                ->get('json')
                ->mediaTypes()
                ->reduce(
                    [],
                    function(array $carry, MediaType $mediaType): array {
                        $carry[(string) $mediaType] = $mediaType->priority();

                        return $carry;
                    }
                )
        );
    }
}
