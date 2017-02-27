<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\EventListener\GenerateHttpExceptionResponseListener;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseForExceptionEvent,
    HttpKernel\HttpKernelInterface,
    HttpKernel\Exception\HttpExceptionInterface,
    HttpFoundation\Request
};
use PHPUnit\Framework\TestCase;

class GenerateHttpExceptionResponseListenerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            new GenerateHttpExceptionResponseListener(true)
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::EXCEPTION => [['generateResponse', -50]]],
            GenerateHttpExceptionResponseListener::getSubscribedEvents()
        );
    }

    public function testDoesntGenerateWhenNotHttpException()
    {
        $listener = new GenerateHttpExceptionResponseListener(false);
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            new \Exception
        );

        $this->assertNull($listener->generateResponse($event));
        $this->assertFalse($event->hasResponse());
    }

    public function testDoesntGenerateWhenInDebugMode()
    {
        $listener = new GenerateHttpExceptionResponseListener(true);
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            new class extends \Exception implements HttpExceptionInterface {
                public function getStatusCode()
                {
                    return 400;
                }

                public function getHeaders()
                {
                    return [];
                }
            }
        );

        $this->assertNull($listener->generateResponse($event));
        $this->assertFalse($event->hasResponse());
    }

    public function testGenerateResponse()
    {
        $listener = new GenerateHttpExceptionResponseListener(false);
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            new class extends \Exception implements HttpExceptionInterface {
                public function getStatusCode()
                {
                    return 400;
                }

                public function getHeaders()
                {
                    return [];
                }
            }
        );

        $this->assertNull($listener->generateResponse($event));
        $this->assertTrue($event->hasResponse());
    }
}
