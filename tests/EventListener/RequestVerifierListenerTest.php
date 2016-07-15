<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\EventListener\RequestVerifierListener;
use Innmind\Rest\Server\{
    Request\Verifier\VerifierInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\{
    Map,
    Collection
};
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseEvent,
    HttpKernel\HttpKernelInterface,
    HttpFoundation\Request
};

class RequestVerifierListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $listener = new RequestVerifierListener(
            $this->createMock(VerifierInterface::class)
        );

        $this->assertInstanceOf(EventSubscriberInterface::class, $listener);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::REQUEST => [['verifyRequest', 24]]],
            RequestVerifierListener::getSubscribedEvents()
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage verified
     */
    public function testVerify()
    {
        $listener = new RequestVerifierListener(
            $verifier = $this->createMock(VerifierInterface::class)
        );
        $verifier
            ->method('verify')
            ->will($this->throwException(new \Exception('verified')));
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $request->attributes->set(
            '_innmind_request',
            $this->createMock(ServerRequestInterface::class)
        );
        $request->attributes->set(
            '_innmind_resource_definition',
            new HttpResource(
                'foo',
                new Identity('foo'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('foo'),
                false,
                new Map('string', 'string')
            )
        );

        $listener->verifyRequest($event);
    }

    public function testDoesntVerify()
    {
        $listener = new RequestVerifierListener(
            $verifier = $this->createMock(VerifierInterface::class)
        );
        $verifier
            ->method('verify')
            ->will($this->throwException(new \Exception('verified')));
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->assertSame(null, $listener->verifyRequest($event));
    }
}
