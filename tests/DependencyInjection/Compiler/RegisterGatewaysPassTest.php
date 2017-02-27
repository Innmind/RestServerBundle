<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\DependencyInjection\{
    InnmindRestServerExtension,
    Compiler\RegisterGatewaysPass
};
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface,
    Reference,
    Definition
};
use PHPUnit\Framework\TestCase;

class RegisterGatewaysPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder;
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $container->setDefinition(
            'foo',
            (new Definition)->addTag(
                'innmind_rest_server.gateway',
                ['alias' => 'command']
            )
        );
        $pass = new RegisterGatewaysPass;

        $this->assertInstanceOf(CompilerPassInterface::class, $pass);
        $this->assertSame(null, $pass->process($container));
        $gateways = $container->getDefinition('innmind_rest_server.gateways');
        $this->assertSame(1, count($gateways->getArgument(0)));
        $this->assertSame('command', key($gateways->getArgument(0)));
        $this->assertInstanceOf(
            Reference::class,
            $gateways->getArgument(0)['command']
        );
        $this->assertSame(
            'foo',
            (string) $gateways->getArgument(0)['command']
        );
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\MissingAliasException
     */
    public function testThrowWhenNoAliasDefinedForAGateway()
    {
        $container = new ContainerBuilder;
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $container->setDefinition(
            'foo',
            (new Definition)->addTag('innmind_rest_server.gateway')
        );
        $pass = new RegisterGatewaysPass;

        $pass->process($container);
    }
}
