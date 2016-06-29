<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\DependencyInjection\{
    InnmindRestServerExtension,
    Compiler\RegisterHttpHeaderFactoriesPass
};
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface,
    Reference,
    Definition
};

class RegisterHttpHeaderFactoriesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder;
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $pass = new RegisterHttpHeaderFactoriesPass;

        $this->assertInstanceOf(CompilerPassInterface::class, $pass);
        $this->assertSame(null, $pass->process($container));
        $argument = $container
            ->getDefinition('innmind_rest_server.http.factory.header.default')
            ->getArgument(0);
        $this->assertSame(12, count($argument));

        foreach ($argument as $key => $value) {
            $this->assertTrue(is_string($key));
            $this->assertInstanceOf(Reference::class, $value);
        }
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
            (new Definition)->addTag('innmind_rest_server.http_header_factory')
        );

        (new RegisterHttpHeaderFactoriesPass)->process($container);
    }
}
