<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\DependencyInjection\{
    InnmindRestServerExtension,
    Compiler\RegisterDefinitionFilesPass
};
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface
};
use Fixtures\Innmind\Rest\ServerBundle\{
    FooBundle\FixtureFooBundle,
    BarBundle\FixtureBarBundle,
    EmptyBundle\FixtureEmptyBundle
};
use PHPUnit\Framework\TestCase;

class RegisterDefinitionFilesPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder;
        $container->setParameter(
            'kernel.bundles',
            [
                'FixtureFooBundle' => FixtureFooBundle::class,
                'FixtureBarBundle' => FixtureBarBundle::class,
                'FixtureEmptyBundle' => FixtureEmptyBundle::class,
            ]
        );
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $pass = new RegisterDefinitionFilesPass;

        $this->assertInstanceOf(CompilerPassInterface::class, $pass);
        $this->assertSame(null, $pass->process($container));
        $directories = $container->getDefinition(
            'innmind_rest_server.definition.directories'
        );
        $files = $directories->getArgument(0);
        $this->assertSame(
            [
                getcwd().'/fixtures/FooBundle/Resources/config/rest.yml',
                getcwd().'/fixtures/BarBundle/Resources/config/rest/resource.yml',
            ],
            $files
        );
    }
}
