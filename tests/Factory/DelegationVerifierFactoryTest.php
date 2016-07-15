<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\Factory\DelegationVerifierFactory;
use Innmind\Rest\Server\{
    Definition\HttpResource,
    Definition\Identity,
    Definition\Gateway,
    Definition\Property,
    Request\Verifier\DelegationVerifier,
    Request\Verifier\VerifierInterface
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\Map;

class DelegationVerifierFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $factory = new DelegationVerifierFactory;

        $verifier = $factory->make([
            50 => $mock = $this->createMock(VerifierInterface::class),
        ]);
        $called = false;
        $mock
            ->method('verify')
            ->will($this->returnCallback(function() use (&$called) {
                $called = true;
            }));

        $this->assertInstanceOf(DelegationVerifier::class, $verifier);
        $verifier->verify(
            $this->createMock(ServerRequestInterface::class),
            new HttpResource(
                'foo',
                new Identity('foo'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('foo'),
                false,
                new Map('string', 'string')
            )
        );
        $this->assertTrue($called);
    }
}
