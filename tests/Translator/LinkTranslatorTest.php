<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Translator;

use Innmind\Rest\ServerBundle\Translator\LinkTranslator;
use Innmind\Rest\Server\{
    Definition\Locator,
    Definition\Loader\YamlLoader,
    Definition\Types,
    Link\Parameter,
    Reference
};
use Innmind\Http\Header\{
    Link,
    LinkValue,
    Parameter as LinkParameterInterface,
    Parameter\Parameter as LinkParameter,
    Value
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Set,
    Map,
    MapInterface
};
use Symfony\Component\Routing\RouterInterface;
use PHPUnit\Framework\TestCase;

class LinkTranslatorTest extends TestCase
{
    public function testTranslate()
    {
        $translator = new LinkTranslator(
            new Locator(
                $directories = (new YamlLoader(new Types))->load(
                    (new Set('string'))->add(
                        'vendor/innmind/rest-server/fixtures/mapping.yml'
                    )
                )
            ),
            $router = $this->createMock(RouterInterface::class)
        );
        $router
            ->method('match')
            ->willReturn([
                '_innmind_resource' => 'top_dir.sub_dir.res',
                'identity' => 'bar',
            ]);

        $references = $translator->translate(
            new Link(
                new LinkValue(
                    Url::fromString('/top_dir/sub_dir/res/bar'),
                    'relationship',
                    (new Map('string', LinkParameterInterface::class))
                        ->put('foo', new LinkParameter('foo', 'baz'))
                )
            )
        );

        $this->assertInstanceOf(MapInterface::class, $references);
        $this->assertSame(Reference::class, (string) $references->keyType());
        $this->assertSame(MapInterface::class, (string) $references->valueType());
        $this->assertCount(1, $references);
        $this->assertSame(
            $directories->get('top_dir')->child('sub_dir')->definition('res'),
            $references->keys()->current()->definition()
        );
        $this->assertSame(
            'bar',
            (string) $references->keys()->current()->identity()
        );
        $parameters = $references->values()->first();
        $this->assertSame('string', (string) $parameters->keyType());
        $this->assertSame(
            Parameter::class,
            (string) $parameters->valueType()
        );
        $this->assertCount(2, $parameters);
        $this->assertSame(
            ['foo', 'rel'],
            $parameters->keys()->toPrimitive()
        );
        $this->assertSame('baz', $parameters->get('foo')->value());
        $this->assertSame('relationship', $parameters->get('rel')->value());
    }

    /**
     * @expectedException Innmind\Rest\ServerBundle\Exception\UnexpectedValueException
     */
    public function testThrowWhenLinkNotFound()
    {
        $translator = new LinkTranslator(
            new Locator(
                (new YamlLoader(new Types))->load(
                    (new Set('string'))->add(
                        'vendor/innmind/rest-server/fixtures/mapping.yml'
                    )
                )
            ),
            $router = $this->createMock(RouterInterface::class)
        );
        $router
            ->method('match')
            ->willReturn([]);

        $translator->translate(
            new Link(
                new LinkValue(
                    Url::fromString('/top_dir/sub_dir/res/bar'),
                    'relationship',
                    (new Map('string', LinkParameterInterface::class))
                        ->put('foo', new LinkParameter('foo', 'baz'))
                )
            )
        );
    }
}
