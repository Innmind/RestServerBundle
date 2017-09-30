<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle;

use Innmind\Rest\ServerBundle\{
    InnmindRestServerBundle,
    DependencyInjection\Compiler\RegisterDefinitionFilesPass,
    DependencyInjection\Compiler\RegisterGatewaysPass,
    DependencyInjection\Compiler\RegisterHttpHeaderFactoriesPass,
    DependencyInjection\Compiler\RegisterRequestVerifiersPass,
    DependencyInjection\Compiler\RegisterRangeExtractorsPass,
    DependencyInjection\Compiler\RegisterHeaderBuildersPass
};
use Symfony\Component\{
    HttpKernel\Bundle\Bundle,
    DependencyInjection\ContainerBuilder
};
use PHPUnit\Framework\TestCase;

class InnmindRestServerBundleTest extends TestCase
{
    public function testBuild()
    {
        $container = new ContainerBuilder;
        $bundle = new InnmindRestServerBundle;

        $this->assertInstanceOf(Bundle::class, $bundle);
        $this->assertSame(null, $bundle->build($container));
        $passes = $container
            ->getCompilerPassConfig()
            ->getBeforeOptimizationPasses();
        $this->assertSame(14, count($passes));
        array_shift($passes);
        array_shift($passes);
        $this->assertInstanceOf(
            RegisterDefinitionFilesPass::class,
            $passes[0]
        );
        $this->assertInstanceOf(
            RegisterGatewaysPass::class,
            $passes[1]
        );
        $this->assertInstanceOf(
            RegisterHttpHeaderFactoriesPass::class,
            $passes[2]
        );
        $this->assertInstanceOf(
            RegisterRequestVerifiersPass::class,
            $passes[3]
        );
        $this->assertInstanceOf(
            RegisterRangeExtractorsPass::class,
            $passes[4]
        );
        $this->assertInstanceOf(
            RegisterHeaderBuildersPass::class,
            $passes[5]
        );
        $this->assertInstanceOf(
            RegisterHeaderBuildersPass::class,
            $passes[6]
        );
        $this->assertInstanceOf(
            RegisterHeaderBuildersPass::class,
            $passes[7]
        );
        $this->assertInstanceOf(
            RegisterHeaderBuildersPass::class,
            $passes[8]
        );
        $this->assertInstanceOf(
            RegisterHeaderBuildersPass::class,
            $passes[9]
        );
        $this->assertInstanceOf(
            RegisterHeaderBuildersPass::class,
            $passes[10]
        );
        $this->assertInstanceOf(
            RegisterHeaderBuildersPass::class,
            $passes[11]
        );
    }
}
