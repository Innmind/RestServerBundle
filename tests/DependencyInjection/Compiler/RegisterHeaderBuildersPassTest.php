<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\DependencyInjection\{
    InnmindRestServerExtension,
    Compiler\RegisterHeaderBuildersPass
};
use Innmind\Rest\Server\Action;
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
            new RegisterHeaderBuildersPass(Action::list())
        );
    }

    public function testProcess()
    {
        $container = new ContainerBuilder;
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $pass = new RegisterHeaderBuildersPass(Action::list());

        $this->assertSame(null, $pass->process($container));
        $arguments = $container
            ->getDefinition(
                'innmind_rest_server.response.header_builder.list_delegation'
            )
            ->getArgumentS();
        $this->assertSame(3, count($arguments));
        $this->assertInstanceOf(Reference::class, $arguments[0]);
        $this->assertInstanceOf(Reference::class, $arguments[1]);
        $this->assertInstanceOf(Reference::class, $arguments[2]);
        $this->assertSame(
            'innmind_rest_server.response.header_builder.list_content_type',
            (string) $arguments[0]
        );
        $this->assertSame(
            'innmind_rest_server.response.header_builder.list_links',
            (string) $arguments[1]
        );
        $this->assertSame(
            'innmind_rest_server.response.header_builder.list_range',
            (string) $arguments[2]
        );
    }
}
