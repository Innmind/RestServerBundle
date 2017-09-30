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
        $arguments = $container
            ->getDefinition('innmind_rest_server.range_extractor.delegation')
            ->getArguments();
        $this->assertSame(2, count($arguments));
        $this->assertInstanceOf(Reference::class, $arguments[0]);
        $this->assertInstanceOf(Reference::class, $arguments[1]);
        $this->assertSame(
            'innmind_rest_server.range_extractor.header',
            (string) $arguments[0]
        );
        $this->assertSame(
            'innmind_rest_server.range_extractor.query',
            (string) $arguments[1]
        );
    }
}
