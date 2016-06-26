<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\Server\Definition\LoaderInterface;
use Innmind\Immutable\{
    Map,
    SetInterface,
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
     * @param SetInterface<string> $files
     *
     * @return MapInterface<string, Directory>
     */
    public function make(SetInterface $files): MapInterface
    {
        return $this->loader->load($files);
    }
}
