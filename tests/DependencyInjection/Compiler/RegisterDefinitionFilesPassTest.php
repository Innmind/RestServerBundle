<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\DependencyInjection\{
    InnmindRestServerExtension,
    Compiler\RegisterDefinitionFilesPass
};
use Innmind\Immutable\SetInterface;
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface
};
use Fixtures\Innmind\Rest\ServerBundle\{
    FooBundle\FixtureFooBundle,
    BarBundle\FixtureBarBundle,
    EmptyBundle\FixtureEmptyBundle
};

class RegisterDefinitionFilesPassTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf(SetInterface::class, $files);
        $this->assertSame('string', (string) $files->type());
        $this->assertSame(2, $files->count());
        $this->assertSame(
            getcwd().'/fixtures/FooBundle/Resources/config/rest.yml',
            $files->current()
        );
        $files->next();
        $this->assertSame(
            getcwd().'/fixtures/BarBundle/Resources/config/rest/resource.yml',
            $files->current()
        );
    }
}
