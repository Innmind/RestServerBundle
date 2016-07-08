<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\Server\Response\HeaderBuilder\{
    ListDelegationBuilder,
    ListBuilderInterface
};
use Innmind\Immutable\Set;

final class ListDelegationBuilderFactory
{
    public function make(array $builders): ListDelegationBuilder
    {
        $set = new Set(ListBuilderInterface::class);

        foreach ($builders as $builder) {
            $set = $set->add($builder);
        }

        return new ListDelegationBuilder($set);
    }
}
