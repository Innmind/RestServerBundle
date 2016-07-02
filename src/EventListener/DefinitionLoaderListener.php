<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\Exception\{
    InvalidArgumentException,
    DefinitionNotFoundException
};
use Innmind\Rest\Server\Definition\{
    Directory,
    HttpResource
};
use Innmind\Immutable\MapInterface;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseEvent
};

final class DefinitionLoaderListener implements EventSubscriberInterface
{
    private $directories;

    public function __construct(MapInterface $directories)
    {
        if (
            (string) $directories->keyType() !== 'string' ||
            (string) $directories->valueType() !== Directory::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->directories = $directories;
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
        $resource = $this
            ->directories
            ->reduce(
                null,
                function($carry, string $dirName, Directory $directory) use ($name) {
                    if ($carry instanceof $directory) {
                        return $carry;
                    }

                    $resources = $directory->flatten();

                    if ($resources->contains($name)) {
                        return $resources->get($name);
                    }
                }
            );

        if (!$resource instanceof HttpResource) {
            throw new DefinitionNotFoundException;
        }

        $request->attributes->set(
            '_innmind_resource_definition',
            $resource
        );
    }
}
