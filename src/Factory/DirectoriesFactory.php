<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\Server\Definition\LoaderInterface;
use Innmind\Immutable\{
    Map,
    Set,
    MapInterface
};

final class DirectoriesFactory
{
    private $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param string[] $files
     *
     * @return MapInterface<string, Directory>
     */
    public function make(array $files): MapInterface
    {
        $set = new Set('string');

        foreach ($files as $file) {
            $set = $set->add($file);
        }

        return $this->loader->load($set);
    }
}
