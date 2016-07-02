<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\Translator\RequestTranslator;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseEvent
};

final class TranslateRequestListener implements EventSubscriberInterface
{
    private $translator;

    public function __construct(RequestTranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['translate', 28]],
        ];
    }

    public function translate(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $request->attributes->set(
            '_innmind_request',
            $this->translator->translate($request)
        );
    }
}
