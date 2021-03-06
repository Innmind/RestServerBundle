<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\EventListener\RequestVerifierListener;
use Innmind\Rest\Server\{
    Request\Verifier\Verifier,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\Map;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseEvent,
    HttpKernel\HttpKernelInterface,
    HttpFoundation\Request
};
use PHPUnit\Framework\TestCase;

class RequestVerifierListenerTest extends TestCase
{
    public function testInterface()
    {
        $listener = new RequestVerifierListener(
            $this->createMock(Verifier::class)
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
            $verifier = $this->createMock(Verifier::class)
        );
        $verifier
            ->method('__invoke')
            ->will($this->throwException(new \Exception('verified')));
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $request->attributes->set(
            '_innmind_request',
            $this->createMock(ServerRequest::class)
        );
        $request->attributes->set(
            '_innmind_resource_definition',
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

        $listener->verifyRequest($event);
    }

    public function testDoesntVerify()
    {
        $listener = new RequestVerifierListener(
            $verifier = $this->createMock(Verifier::class)
        );
        $verifier
            ->method('__invoke')
            ->will($this->throwException(new \Exception('verified')));
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->assertSame(null, $listener->verifyRequest($event));
    }
}
