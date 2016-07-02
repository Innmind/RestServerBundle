<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\Server\RequestVerifier\{
    DelegationVerifier,
    VerifierInterface
};
use Innmind\Immutable\Map;

final class DelegationVerifierFactory
{
    /**
     * @param array $verifiers
     *
     * @return DelegationVerifier
     */
    public function make(array $verifiers): DelegationVerifier
    {
        $map = new Map('int', VerifierInterface::class);

        foreach ($verifiers as $priority => $verifier) {
            $map = $map->put($priority, $verifier);
        }

        return new DelegationVerifier($map);
    }
}
