<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Rest\ServerBundle\DependencyInjection\{
    Compiler\RegisterRequestVerifiersPass,
    InnmindRestServerExtension
};
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface,
    Reference,
    Definition
};

class RegisterRequestVerifiersPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder;
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $pass = new RegisterRequestVerifiersPass;

        $this->assertInstanceOf(CompilerPassInterface::class, $pass);
        $this->assertSame(null, $pass->process($container));
        $argument = $container
            ->getDefinition('innmind_rest_server.http.request.verifier')
            ->getArgument(0);
        $this->assertSame(4, count($argument));
        $this->assertSame(
            [100, 75, 50, 25],
            array_keys($argument)
        );
        $this->assertInstanceOf(
            Reference::class,
            $argument[100]
        );
        $this->assertInstanceOf(
            Reference::class,
            $argument[75]
        );
        $this->assertInstanceOf(
            Reference::class,
            $argument[50]
        );
        $this->assertInstanceOf(
            Reference::class,
            $argument[25]
        );
        $this->assertSame(
            'innmind_rest_server.http.request.verifier.accept',
            (string) $argument[100]
        );
        $this->assertSame(
            'innmind_rest_server.http.request.verifier.content_type',
            (string) $argument[75]
        );
        $this->assertSame(
            'innmind_rest_server.http.request.verifier.range',
            (string) $argument[50]
        );
        $this->assertSame(
            'innmind_rest_server.http.request.verifier.link',
            (string) $argument[25]
        );
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\MissingPriorityException
     */
    public function testThrowWhenNoPriorityDefinedForVerifier()
    {
        $container = new ContainerBuilder;
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $pass = new RegisterRequestVerifiersPass;
        $container->setDefinition(
            'foo',
            (new Definition)->addTag('innmind_rest_server.http.request.verifier')
        );

        $pass->process($container);
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\PriorityAlreadyUsedByAVerifierException
     * @expectedExceptionMessage 50
     */
    public function testThrowWhenPriorityUsedTwice()
    {
        $container = new ContainerBuilder;
        (new InnmindRestServerExtension)->load(
            [],
            $container
        );
        $pass = new RegisterRequestVerifiersPass;
        $container->setDefinition(
            'foo',
            (new Definition)->addTag(
                'innmind_rest_server.http.request.verifier',
                ['priority' => '50']
            )
        );

        $pass->process($container);
    }
}
