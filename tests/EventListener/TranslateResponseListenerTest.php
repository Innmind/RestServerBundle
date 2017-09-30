<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\{
    EventListener\TranslateResponseListener,
    Translator\ResponseTranslator
};
use Innmind\Http\{
    Message\Response\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
    Header,
    Header\ContentType,
    Header\ContentTypeValue
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\Map;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseForControllerResultEvent,
    HttpKernel\HttpKernelInterface,
    HttpFoundation\Request
};
use PHPUnit\Framework\TestCase;

class TranslateResponseListenerTest extends TestCase
{
    public function testInterface()
    {
        $listener = new TranslateResponseListener(new ResponseTranslator);

        $this->assertInstanceOf(EventSubscriberInterface::class, $listener);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::VIEW => [['translate', -255]]],
            TranslateResponseListener::getSubscribedEvents()
        );
    }

    public function testTranslate()
    {
        $listener = new TranslateResponseListener(new ResponseTranslator);
        $event = new GetResponseForControllerResultEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            new Response(
                new StatusCode(200),
                new ReasonPhrase('OK'),
                new ProtocolVersion(1, 1),
                new Headers(
                    (new Map('string', Header::class))
                        ->put(
                            'content-type',
                            new ContentType(
                                new ContentTypeValue(
                                    'application',
                                    'json'
                                )
                            )
                        )
                ),
                new StringStream('{"answer":42}')
            )
        );

        $this->assertSame(null, $listener->translate($event));
        $this->assertTrue($event->hasResponse());
    }

    public function testDoesntTranslate()
    {
        $listener = new TranslateResponseListener(new ResponseTranslator);
        $event = new GetResponseForControllerResultEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request,
            HttpKernelInterface::MASTER_REQUEST,
            null
        );

        $this->assertSame(null, $listener->translate($event));
        $this->assertFalse($event->hasResponse());
    }
}
