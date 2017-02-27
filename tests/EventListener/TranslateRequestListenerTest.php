<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\{
    EventListener\TranslateRequestListener,
    Translator\RequestTranslator
};
use Innmind\Http\{
    Factory\Header\DefaultFactory,
    Factory\HeaderFactoryInterface,
    Message\ServerRequestInterface
};
use Innmind\Immutable\Map;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseEvent,
    HttpKernel\HttpKernelInterface,
    HttpFoundation\Request
};
use PHPUnit\Framework\TestCase;

class TranslateRequestListenerTest extends TestCase
{
    public function testInterface()
    {
        $listener = new TranslateRequestListener(
            new RequestTranslator(
                new DefaultFactory(
                    new Map('string', HeaderFactoryInterface::class)
                )
            )
        );

        $this->assertInstanceOf(EventSubscriberInterface::class, $listener);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::REQUEST => [['translate', 28]]],
            TranslateRequestListener::getSubscribedEvents()
        );
    }

    public function testTranslate()
    {
        $listener = new TranslateRequestListener(
            new RequestTranslator(
                new DefaultFactory(
                    new Map('string', HeaderFactoryInterface::class)
                )
            )
        );
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request(
                [],
                [],
                [],
                [],
                [],
                [
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'SERVER_NAME' => 'innmind',
                    'REQUEST_URI' => 'foo',
                    'REQUEST_METHOD' => 'PUT',
                ]
            ),
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->assertSame(null, $listener->translate($event));
        $this->assertInstanceOf(
            ServerRequestInterface::class,
            $request->attributes->get('_innmind_request')
        );
    }
}
