<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\{
    EventListener\TranslateResponseListener,
    Translator\ResponseTranslator
};
use Innmind\Http\{
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    ProtocolVersion,
    Headers,
    Header\HeaderInterface,
    Header\ContentType,
    Header\ContentTypeValue,
    Header\ParameterInterface
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

class TranslateResponseListenerTest extends \PHPUnit_Framework_TestCase
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
                    (new Map('string', HeaderInterface::class))
                        ->put(
                            'content-type',
                            new ContentType(
                                new ContentTypeValue(
                                    'application',
                                    'json',
                                    new Map('string', ParameterInterface::class)
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
