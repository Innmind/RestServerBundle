<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\Server\Definition\Locator;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseEvent
};

final class DefinitionLoaderListener implements EventSubscriberInterface
{
    private $locator;

    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['loadDefinition', 30]],
        ];
    }

    /**
     * Inject in the request attributes the resource definition
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function loadDefinition(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_innmind_resource')) {
            return;
        }

        $name = $request->attributes->get('_innmind_resource');
        $request->attributes->set(
            '_innmind_resource_definition',
            $this->locator->locate($name)
        );
    }
}
