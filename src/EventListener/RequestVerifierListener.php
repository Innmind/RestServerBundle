<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\Server\Request\Verifier\Verifier;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseEvent
};

final class RequestVerifierListener implements EventSubscriberInterface
{
    private $verify;

    public function __construct(Verifier $verifier)
    {
        $this->verify = $verifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['verifyRequest', 24]],
        ];
    }

    public function verifyRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_innmind_resource_definition')) {
            return;
        }

        ($this->verify)(
            $request->attributes->get('_innmind_request'),
            $request->attributes->get('_innmind_resource_definition')
        );
    }
}
