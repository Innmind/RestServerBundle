<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\Server\RequestVerifier\VerifierInterface;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseEvent
};

final class RequestVerifierListener implements EventSubscriberInterface
{
    private $verifier;

    public function __construct(VerifierInterface $verifier)
    {
        $this->verifier = $verifier;
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

        $this->verifier->verify(
            $request->attributes->get('_innmind_request'),
            $request->attributes->get('_innmind_resource_definition')
        );
    }
}
