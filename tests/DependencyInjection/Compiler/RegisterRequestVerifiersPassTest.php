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
use PHPUnit\Framework\TestCase;

class RegisterRequestVerifiersPassTest extends TestCase
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
        $arguments = $container
            ->getDefinition('innmind_rest_server.http.request.verifier')
            ->getArguments();
        $this->assertSame(4, count($arguments));
        $this->assertInstanceOf(
            Reference::class,
            $arguments[0]
        );
        $this->assertInstanceOf(
            Reference::class,
            $arguments[1]
        );
        $this->assertInstanceOf(
            Reference::class,
            $arguments[2]
        );
        $this->assertInstanceOf(
            Reference::class,
            $arguments[3]
        );
        $this->assertSame(
            'innmind_rest_server.http.request.verifier.accept',
            (string) $arguments[0]
        );
        $this->assertSame(
            'innmind_rest_server.http.request.verifier.content_type',
            (string) $arguments[1]
        );
        $this->assertSame(
            'innmind_rest_server.http.request.verifier.range',
            (string) $arguments[2]
        );
        $this->assertSame(
            'innmind_rest_server.http.request.verifier.link',
            (string) $arguments[3]
        );
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\MissingPriority
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
     * @expectedException Innmind\Rest\ServerBundle\Exception\PriorityAlreadyUsedByAVerifier
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
