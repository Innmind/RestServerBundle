<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\DependencyInjection\{
    InnmindRestServerExtension,
    Compiler\RegisterHeaderBuildersPass
};
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Reference,
    Compiler\CompilerPassInterface
};
use PHPUnit\Framework\TestCase;

class RegisterHeaderBuildersPassTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            CompilerPassInterface::class,
            new RegisterHeaderBuildersPass('list')
        );
    }

    public function testProcess()
    {
        $container = new ContainerBuilder;
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $pass = new RegisterHeaderBuildersPass('list');

        $this->assertSame(null, $pass->process($container));
        $argument = $container
            ->getDefinition(
                'innmind_rest_server.response.header_builder.list_delegation'
            )
            ->getArgument(0);
        $this->assertSame(3, count($argument));
        $this->assertInstanceOf(Reference::class, $argument[0]);
        $this->assertInstanceOf(Reference::class, $argument[1]);
        $this->assertInstanceOf(Reference::class, $argument[2]);
        $this->assertSame(
            'innmind_rest_server.response.header_builder.list_content_type',
            (string) $argument[0]
        );
        $this->assertSame(
            'innmind_rest_server.response.header_builder.list_links',
            (string) $argument[1]
        );
        $this->assertSame(
            'innmind_rest_server.response.header_builder.list_range',
            (string) $argument[2]
        );
    }
}
