<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\Factory\DelegationVerifierFactory;
use Innmind\Rest\Server\{
    Definition\HttpResource,
    Definition\Identity,
    Definition\Gateway,
    Definition\Property,
    RequestVerifier\DelegationVerifier,
    RequestVerifier\VerifierInterface
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\{
    Map,
    Collection
};

class DelegationVerifierFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $factory = new DelegationVerifierFactory;

        $verifier = $factory->make([
            50 => $mock = $this->getMock(VerifierInterface::class),
        ]);
        $called = false;
        $mock
            ->method('verify')
            ->will($this->returnCallback(function() use (&$called) {
                $called = true;
            }));

        $this->assertInstanceOf(DelegationVerifier::class, $verifier);
        $verifier->verify(
            $this->getMock(ServerRequestInterface::class),
            new HttpResource(
                'foo',
                new Identity('foo'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('foo'),
                false
            )
        );
        $this->assertTrue($called);
    }
}
