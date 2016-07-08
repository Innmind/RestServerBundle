<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\EventListener\ExceptionListener;
use Innmind\Rest\Server\Exception\{
    HttpResourceDenormalizationException,
    ActionNotImplementedException,
    DenormalizationException,
    FilterNotApplicableException
};
use Innmind\Http\Exception\{
    Http\BadRequestException,
    ExceptionInterface as BaseHttpExceptionInterface
};
use Innmind\Immutable\Map;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseForExceptionEvent,
    HttpKernel\HttpKernelInterface,
    HttpKernel\Exception\HttpException,
    HttpFoundation\Request
};

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            new ExceptionListener
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                KernelEvents::EXCEPTION => [['transformException', 100]],
            ],
            ExceptionListener::getSubscribedEvents()
        );
    }

    public function testTransformException()
    {
        $exception = new BadRequestException;
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );
        $listener = new ExceptionListener;

        $this->assertSame(
            null,
            $listener->transformException($event)
        );
        $this->assertInstanceOf(
            HttpException::class,
            $event->getException()
        );
        $this->assertSame(400, $event->getException()->getStatusCode());
        $this->assertSame($exception, $event->getException()->getPrevious());
    }

    public function testDoesntTransformException()
    {
        $exception = new \Exception;
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );
        $listener = new ExceptionListener;

        $this->assertSame(
            null,
            $listener->transformException($event)
        );
        $this->assertSame($exception, $event->getException());
    }

    public function testTransformActionNotImplemented()
    {
        $exception = new ActionNotImplementedException;
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );
        $listener = new ExceptionListener;

        $this->assertSame(
            null,
            $listener->transformException($event)
        );
        $this->assertInstanceOf(
            HttpException::class,
            $event->getException()
        );
        $this->assertSame(405, $event->getException()->getStatusCode());
    }

    public function testTransformDenormalizationException()
    {
        $exception = new HttpResourceDenormalizationException(
            new Map('string', DenormalizationException::class)
        );
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );
        $listener = new ExceptionListener;

        $this->assertSame(
            null,
            $listener->transformException($event)
        );
        $this->assertInstanceOf(
            HttpException::class,
            $event->getException()
        );
        $this->assertSame(400, $event->getException()->getStatusCode());
    }

    public function testTransformFilterNotApplicableException()
    {
        $exception = new FilterNotApplicableException;
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );
        $listener = new ExceptionListener;

        $this->assertSame(
            null,
            $listener->transformException($event)
        );
        $this->assertInstanceOf(
            HttpException::class,
            $event->getException()
        );
        $this->assertSame(400, $event->getException()->getStatusCode());
    }

    public function testTransformAnyBaseHttpException()
    {
        $exception = new class extends \Exception implements BaseHttpExceptionInterface
        {
        };
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );
        $listener = new ExceptionListener;

        $this->assertSame(
            null,
            $listener->transformException($event)
        );
        $this->assertInstanceOf(
            HttpException::class,
            $event->getException()
        );
        $this->assertSame(400, $event->getException()->getStatusCode());
    }
}
