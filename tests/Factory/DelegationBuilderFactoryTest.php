<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\ServerBundle\Factory\DelegationBuilderFactory;
use Innmind\Rest\Server\{
    Response\HeaderBuilder\ListDelegationBuilder,
    Response\HeaderBuilder\ListBuilderInterface,
    IdentityInterface,
    Definition\Httpresource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Header\HeaderInterface
};
use Innmind\Immutable\{
    Map,
    Set,
    Collection
};

class DelegationBuilderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $factory = new DelegationBuilderFactory(
            ListDelegationBuilder::class,
            ListBuilderInterface::class
        );

        $builder = $factory->make([
            $mock = $this->createMock(ListBuilderInterface::class)
        ]);
        $mock
            ->method('build')
            ->willReturn(
                (new Map('string', HeaderInterface::class))
                    ->put('foo', $this->createMock(HeaderInterface::class))
            );

        $this->assertInstanceOf(ListDelegationBuilder::class, $builder);
        $this->assertSame(
            1,
            $builder
                ->build(
                    new Set(IdentityInterface::class),
                    $this->createMock(ServerRequestInterface::class),
                    new Httpresource(
                        'foobar',
                        new Identity('foo'),
                        new Map('string', Property::class),
                        new Collection([]),
                        new Collection([]),
                        new Gateway('bar'),
                        true
                    )
                )
                ->size()
        );
    }
}
