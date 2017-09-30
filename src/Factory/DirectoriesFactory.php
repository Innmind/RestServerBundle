<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\Server\Definition\Loader;
use Innmind\Immutable\{
    Set,
    MapInterface
};

final class DirectoriesFactory
{
    private $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param array<string> $files
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
