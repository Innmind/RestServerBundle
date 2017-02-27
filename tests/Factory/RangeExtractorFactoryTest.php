<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\Factory\RangeExtractorFactory;
use Innmind\Rest\Server\{
    RangeExtractor\DelegationExtractor,
    RangeExtractor\ExtractorInterface,
    Request\Range
};
use Innmind\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

class RangeExtractorFactoryTest extends TestCase
{
    public function testMake()
    {
        $factory = new RangeExtractorFactory;

        $extractor = $factory->make([
            $extractor1 = $this->createMock(ExtractorInterface::class)
        ]);
        $extractor1
            ->method('extract')
            ->willReturn(new Range(0, 42));

        $this->assertInstanceOf(DelegationExtractor::class, $extractor);
        $extractor->extract(
            $this->createMock(ServerRequestInterface::class)
        );
    }
}
