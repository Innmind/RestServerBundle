<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Translator;

use Innmind\Rest\ServerBundle\Exception\UnexpectedValueException;
use Innmind\Rest\Server\{
    Definition\Locator,
    Reference,
    Identity,
    Link\ParameterInterface,
    Link\Parameter
};
use Innmind\Http\Header\{
    Link,
    LinkValue,
    ParameterInterface as LinkParameterInterface
};
use Innmind\Immutable\{
    MapInterface,
    Map
};
use Symfony\Component\Routing\RouterInterface;

final class LinkTranslator
{
    private $locator;
    private $router;

    public function __construct(Locator $locator, RouterInterface $router)
    {
        $this->locator = $locator;
        $this->router = $router;
    }

    /**
     * @return MapInterface<Reference, MapInterface<string, ParameterInterface>>
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
     * @return array<Reference, MapInterface<string, ParameterInterface>>
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
                $this->locator->locate($infos['_innmind_resource']),
                new Identity($infos['identity'])
            ),
            $this
                ->translateParameters($link->parameters())
                ->put('rel', new Parameter('rel', $link->relationship()))
        ];
    }

    /**
     * @return MapInterface<string, ParameterInterface>
     */
    private function translateParameters(MapInterface $parameters): MapInterface
    {
        return $parameters->reduce(
            new Map('string', ParameterInterface::class),
            function(Map $carry, string $name, LinkParameterInterface $param): Map {
                return $carry->put(
                    $name,
                    new Parameter($name, $param->value())
                );
            }
        );
    }
}
