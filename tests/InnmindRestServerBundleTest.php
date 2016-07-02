<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle;

use Innmind\Rest\ServerBundle\{
    InnmindRestServerBundle,
    DependencyInjection\Compiler\RegisterDefinitionFilesPass,
    DependencyInjection\Compiler\RegisterGatewaysPass,
    DependencyInjection\Compiler\RegisterHttpHeaderFactoriesPass,
    DependencyInjection\Compiler\RegisterRequestVerifiersPass
};
use Symfony\Component\{
    HttpKernel\Bundle\Bundle,
    DependencyInjection\ContainerBuilder
};

class InnmindRestServerBundleTest extends \PHPUnit_Framework_TestCase
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
        $this->assertSame(4, count($passes));
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
    }
}
