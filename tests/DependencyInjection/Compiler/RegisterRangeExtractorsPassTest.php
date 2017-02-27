<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\DependencyInjection\{
    InnmindRestServerExtension,
    Compiler\RegisterRangeExtractorsPass
};
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Reference,
    Compiler\CompilerPassInterface
};
use PHPUnit\Framework\TestCase;

class RegisterRangeExtractorsPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder;
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $pass = new RegisterRangeExtractorsPass;

        $this->assertInstanceOf(CompilerPassInterface::class, $pass);
        $this->assertSame(null, $pass->process($container));
        $argument = $container
            ->getDefinition('innmind_rest_server.range_extractor.delegation')
            ->getArgument(0);
        $this->assertSame(2, count($argument));
        $this->assertInstanceOf(Reference::class, $argument[0]);
        $this->assertInstanceOf(Reference::class, $argument[1]);
        $this->assertSame(
            'innmind_rest_server.range_extractor.header',
            (string) $argument[0]
        );
        $this->assertSame(
            'innmind_rest_server.range_extractor.query',
            (string) $argument[1]
        );
    }
}
