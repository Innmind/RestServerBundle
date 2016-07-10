<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Immutable\Set;

final class DelegationBuilderFactory
{
    private $class;
    private $interface;

    public function __construct(string $class, string $interface)
    {
        $this->class = $class;
        $this->interface = $interface;
    }

    public function make(array $builders)
    {
        $set = new Set($this->interface);

        foreach ($builders as $builder) {
            $set = $set->add($builder);
        }

        return new $this->class($set);
    }
}
