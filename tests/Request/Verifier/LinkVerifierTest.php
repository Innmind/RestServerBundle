<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Request\Verifier;

use Innmind\Rest\ServerBundle\Request\Verifier\LinkVerifier;
use Innmind\Rest\Server\{
    Request\Verifier\VerifierInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
    ProtocolVersionInterface,
    Headers,
    Header\HeaderInterface,
    Header\HeaderValueInterface,
    Header\Link,
    Header\LinkValue,
    Header\ParameterInterface,
    Message\EnvironmentInterface,
    Message\CookiesInterface,
    Message\QueryInterface,
    Message\FormInterface,
    Message\FilesInterface
};
use Innmind\Url\{
    UrlInterface,
    Url
};
use Innmind\Filesystem\StreamInterface;
use Innmind\Immutable\{
    Map,
    Set
};
use Symfony\Component\Routing\RouterInterface;

class LinkVerifierTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            VerifierInterface::class,
            new LinkVerifier(
                $this->createMock(RouterInterface::class)
            )
        );
    }

    public function testVerify()
    {
        $definition = new HttpResource(
            'name',
            new Identity('foo'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('foo'),
            false,
            (new Map('string', 'string'))
                ->put('foo', 'bar')
        );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('LINK'),
            $this->createMock(ProtocolVersionInterface::class),
            new Headers(
                (new Map('string', HeaderInterface::class))
                    ->put(
                        'Link',
                        new Link(
                            (new Set(HeaderValueInterface::class))
                                ->add(
                                    new LinkValue(
                                        Url::fromString('/foo'),
                                        'bar',
                                        new Map('string', ParameterInterface::class)
                                    )
                                )
                        )
                    )
            ),
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            $this->createMock(QueryInterface::class),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
        );
        $router = $this->createMock(RouterINterface::class);
        $router
            ->method('match')
            ->willReturn([
                '_innmind_resource' => 'foo',
                'identity' => 'foo',
            ]);
        $verifier = new LinkVerifier($router);

        $this->assertNull($verifier->verify($request, $definition));
    }

    public function testDoesntVerify()
    {
        $definition = new HttpResource(
            'name',
            new Identity('foo'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('foo'),
            false,
            new Map('string', 'string')
        );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('POST'),
            $this->createMock(ProtocolVersionInterface::class),
            new Headers(
                (new Map('string', HeaderInterface::class))
                    ->put(
                        'Link',
                        new Link(
                            (new Set(HeaderValueInterface::class))
                                ->add(
                                    new LinkValue(
                                        Url::fromString('/foo'),
                                        'bar',
                                        new Map('string', ParameterInterface::class)
                                    )
                                )
                        )
                    )
            ),
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            $this->createMock(QueryInterface::class),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
        );
        $router = $this->createMock(RouterINterface::class);
        $router
            ->method('match')
            ->willReturn([
                '_innmind_resource' => 'foo',
                'identity' => 'foo',
            ]);
        $verifier = new LinkVerifier($router);

        $this->assertNull($verifier->verify($request, $definition));
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\BadRequestException
     */
    public function testThrowIfTargetResourceIsNotARestResource()
    {
        $definition = new HttpResource(
            'name',
            new Identity('foo'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('foo'),
            false,
            (new Map('string', 'string'))
                ->put('foo', 'bar')
        );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('LINK'),
            $this->createMock(ProtocolVersionInterface::class),
            new Headers(
                (new Map('string', HeaderInterface::class))
                    ->put(
                        'Link',
                        new Link(
                            (new Set(HeaderValueInterface::class))
                                ->add(
                                    new LinkValue(
                                        Url::fromString('/foo'),
                                        'bar',
                                        new Map('string', ParameterInterface::class)
                                    )
                                )
                        )
                    )
            ),
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            $this->createMock(QueryInterface::class),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
        );
        $router = $this->createMock(RouterINterface::class);
        $router
            ->method('match')
            ->willReturn([]);
        $verifier = new LinkVerifier($router);

        $verifier->verify($request, $definition);
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\BadRequestException
     */
    public function testThrowIfTargetResourceIsNotAllowed()
    {
        $definition = new HttpResource(
            'name',
            new Identity('foo'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('foo'),
            false,
            (new Map('string', 'string'))
                ->put('foo', 'baz')
        );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('LINK'),
            $this->createMock(ProtocolVersionInterface::class),
            new Headers(
                (new Map('string', HeaderInterface::class))
                    ->put(
                        'Link',
                        new Link(
                            (new Set(HeaderValueInterface::class))
                                ->add(
                                    new LinkValue(
                                        Url::fromString('/foo'),
                                        'bar',
                                        new Map('string', ParameterInterface::class)
                                    )
                                )
                        )
                    )
            ),
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            $this->createMock(QueryInterface::class),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
        );
        $router = $this->createMock(RouterINterface::class);
        $router
            ->method('match')
            ->willReturn([
                '_innmind_resource' => 'foo',
                'identity' => 'foo',
            ]);
        $verifier = new LinkVerifier($router);

        $verifier->verify($request, $definition);
    }
}
