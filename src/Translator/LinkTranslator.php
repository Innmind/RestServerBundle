<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Translator;

use Innmind\Rest\ServerBundle\Exception\UnexpectedValueException;
use Innmind\Rest\Server\{
    Definition\Locator,
    Reference,
    Identity\Identity,
    Link\Parameter
};
use Innmind\Http\Header\{
    Link,
    LinkValue,
    Parameter as HttpParameter
};
use Innmind\Immutable\{
    MapInterface,
    Map
};
use Symfony\Component\Routing\RouterInterface;

final class LinkTranslator
{
    private $locate;
    private $router;

    public function __construct(Locator $locator, RouterInterface $router)
    {
        $this->locate = $locator;
        $this->router = $router;
    }

    /**
     * @return MapInterface<Reference, MapInterface<string, Parameter>>
     */
    public function translate(Link $link): MapInterface
    {
        return $link
            ->values()
            ->reduce(
                new Map(Reference::class, MapInterface::class),
                function(Map $carry, LinkValue $link): Map {
                    list($reference, $parameters) = $this->translateLinkValue($link);

                    return $carry->put($reference, $parameters);
                }
            );
    }

    /**
     * @return array<Reference, MapInterface<string, Parameter>>
     */
    private function translateLinkValue(LinkValue $link): array
    {
        $infos = $this->router->match((string) $link->url());

        if (
            !isset($infos['_innmind_resource']) ||
            !isset($infos['identity'])
        ) {
            throw new UnexpectedValueException;
        }

        return [
            new Reference(
                ($this->locate)($infos['_innmind_resource']),
                new Identity($infos['identity'])
            ),
            $this
                ->translateParameters($link->parameters())
                ->put('rel', new Parameter\Parameter('rel', $link->relationship()))
        ];
    }

    /**
     * @return MapInterface<string, Parameter>
     */
    private function translateParameters(MapInterface $parameters): MapInterface
    {
        return $parameters->reduce(
            new Map('string', Parameter::class),
            function(Map $carry, string $name, HttpParameter $param): Map {
                return $carry->put(
                    $name,
                    new Parameter\Parameter($name, $param->value())
                );
            }
        );
    }
}
