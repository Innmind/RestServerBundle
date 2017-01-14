<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseForExceptionEvent,
    HttpKernel\Exception\HttpExceptionInterface,
    HttpFoundation\Response
};

final class GenerateHttpExceptionResponseListener implements EventSubscriberInterface
{
    private $shouldGenerate;

    public function __construct(bool $debug)
    {
        //let the debug bundle handle exceptions when necessarry
        $this->shouldGenerate = !$debug;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [['generateResponse', -50]],
        ];
    }

    public function generateResponse(GetResponseForExceptionEvent $event)
    {
        if (!$event->getException() instanceof HttpExceptionInterface) {
            return;
        }

        if ($this->shouldGenerate) {
            $event->setResponse(new Response);
        }
    }
}
