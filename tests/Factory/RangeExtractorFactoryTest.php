<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\{
    Factory\RangeExtractorFactory,
    RangeExtractor\DelegationExtractor,
    RangeExtractor\ExtractorInterface
};
use Innmind\Rest\Server\Request\Range;
use Innmind\Http\Message\ServerRequestInterface;

class RangeExtractorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $factory = new RangeExtractorFactory;

        $extractor = $factory->make([
            $extractor1 = $this->getMock(ExtractorInterface::class)
        ]);
        $extractor1
            ->method('extract')
            ->willReturn(new Range(0, 42));

        $this->assertInstanceOf(DelegationExtractor::class, $extractor);
        $extractor->extract(
            $this->getMock(ServerRequestInterface::class)
        );
    }
}
