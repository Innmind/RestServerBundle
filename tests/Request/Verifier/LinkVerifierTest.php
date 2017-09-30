<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Request\Verifier;

use Innmind\Rest\ServerBundle\Request\Verifier\LinkVerifier;
use Innmind\Rest\Server\{
    Request\Verifier\Verifier,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method\Method,
    ProtocolVersion,
    Headers\Headers,
    Header,
    Header\Link,
    Header\LinkValue,
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Form,
    Message\Files
};
use Innmind\Url\{
    UrlInterface,
    Url
};
use Innmind\Stream\Readable;
use Innmind\Immutable\Map;
use Symfony\Component\Routing\RouterInterface;
use PHPUnit\Framework\TestCase;

class LinkVerifierTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Verifier::class,
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
                ->put('bar', 'foo')
        );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('LINK'),
            $this->createMock(ProtocolVersion::class),
            new Headers(
                (new Map('string', Header::class))
                    ->put(
                        'Link',
                        new Link(
                            new LinkValue(
                                Url::fromString('/foo'),
                                'bar'
                            )
                        )
                    )
            ),
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            $this->createMock(Query::class),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );
        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('match')
            ->willReturn([
                '_innmind_resource' => 'foo',
                'identity' => 'foo',
            ]);
        $verify = new LinkVerifier($router);

        $this->assertNull($verify($request, $definition));
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
            $this->createMock(ProtocolVersion::class),
            new Headers(
                (new Map('string', Header::class))
                    ->put(
                        'Link',
                        new Link(
                            new LinkValue(
                                Url::fromString('/foo'),
                                'bar'
                            )
                        )
                    )
            ),
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            $this->createMock(Query::class),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );
        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('match')
            ->willReturn([
                '_innmind_resource' => 'foo',
                'identity' => 'foo',
            ]);
        $verify = new LinkVerifier($router);

        $this->assertNull($verify($request, $definition));
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\BadRequest
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
            $this->createMock(ProtocolVersion::class),
            new Headers(
                (new Map('string', Header::class))
                    ->put(
                        'Link',
                        new Link(
                            new LinkValue(
                                Url::fromString('/foo'),
                                'bar'
                            )
                        )
                    )
            ),
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            $this->createMock(Query::class),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );
        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('match')
            ->willReturn([]);
        $verify = new LinkVerifier($router);

        $verify($request, $definition);
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\BadRequest
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
            $this->createMock(ProtocolVersion::class),
            new Headers(
                (new Map('string', Header::class))
                    ->put(
                        'Link',
                        new Link(
                            new LinkValue(
                                Url::fromString('/foo'),
                                'bar'
                            )
                        )
                    )
            ),
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            $this->createMock(Query::class),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );
        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('match')
            ->willReturn([
                '_innmind_resource' => 'foo',
                'identity' => 'foo',
            ]);
        $verify = new LinkVerifier($router);

        $verify($request, $definition);
    }
}
