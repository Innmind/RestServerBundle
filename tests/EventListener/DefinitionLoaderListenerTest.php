<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\ServerBundle\EventListener\DefinitionLoaderListener;
use Innmind\Rest\Server\Definition\{
    Loader\YamlLoader,
    Types,
    Locator
};
use Innmind\Immutable\Set;
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseEvent,
    HttpKernel\HttpKernelInterface,
    HttpFoundation\Request
};

class DefinitionLoaderListenerTest extends \PHPUnit_Framework_TestCase
{
    private $directories;
    private $locator;

    public function setUp()
    {
        $this->directories = (new YamlLoader(new Types))->load(
            (new Set('string'))->add(
                'vendor/innmind/rest-server/fixtures/mapping.yml'
            )
        );
        $this->locator = new Locator($this->directories);
    }

    public function testInterface()
    {
        $listener = new DefinitionLoaderListener($this->locator);

        $this->assertInstanceOf(EventSubscriberInterface::class, $listener);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::REQUEST => [['loadDefinition', 30]]],
            DefinitionLoaderListener::getSubscribedEvents()
        );
    }

    public function testLoadDefinition()
    {
        $listener = new DefinitionLoaderListener($this->locator);
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $request->attributes->set('_innmind_resource', 'top_dir.image');

        $this->assertSame(null, $listener->loadDefinition($event));
        $this->assertTrue(
            $request->attributes->has('_innmind_resource_definition')
        );
        $this->assertSame(
            $this->directories->get('top_dir')->definitions()->get('image'),
            $request->attributes->get('_innmind_resource_definition')
        );
    }

    public function testDoesntLoadDefinition()
    {
        $listener = new DefinitionLoaderListener($this->locator);
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->assertSame(null, $listener->loadDefinition($event));
        $this->assertFalse(
            $request->attributes->has('_innmind_resource_definition')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DefinitionNotFoundException
     */
    public function testThrowWhenResourceNotFound()
    {
        $listener = new DefinitionLoaderListener($this->locator);
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $request->attributes->set('_innmind_resource', 'foo');

        $listener->loadDefinition($event);
    }
}
