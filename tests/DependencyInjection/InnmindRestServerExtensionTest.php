<?php
declare(stritc_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\DependencyInjection;

use Innmind\Rest\ServerBundle\DependencyInjection\InnmindRestServerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InnmindRestServerExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder;
        $extension = new InnmindRestServerExtension;

        $this->assertSame(
            null,
            $extension->load(
                [[
                    'types' => ['foo'],
                ]],
                $container
            )
        );
        $types = $container->getDefinition('innmind_rest_server.definition.types');
        $this->assertSame(1, count($types->getMethodCalls()));
        $this->assertSame(
            ['register', ['foo']],
            $types->getMethodCalls()[0]
        );
    }
}
