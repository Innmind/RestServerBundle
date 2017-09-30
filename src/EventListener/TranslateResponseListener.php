<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\Translator\ResponseTranslator;
use Innmind\Http\Message\Response;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseForControllerResultEvent
};

final class TranslateResponseListener implements EventSubscriberInterface
{
    private $translator;

    public function __construct(ResponseTranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [['translate', -255]],
        ];
    }

    public function translate(GetResponseForControllerResultEvent $event)
    {
        $data = $event->getControllerResult();

        if (!$data instanceof Response) {
            return;
        }

        $event->setResponse(
            $this->translator->translate($data)
        );
    }
}
