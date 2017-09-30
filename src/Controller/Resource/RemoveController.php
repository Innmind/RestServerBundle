<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\RemoveBuilder,
    Gateway,
    Identity\Identity
};
use Innmind\Http\{
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\MapInterface;
use Symfony\Component\HttpFoundation\Request;

final class RemoveController
{
    private $gateways;
    private $buildHeader;

    public function __construct(
        MapInterface $gateways,
        RemoveBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type MapInterface<string, %s>',
                Gateway::class
            ));
        }

        $this->gateways = $gateways;
        $this->buildHeader = $headerBuilder;
    }

    public function defaultAction(Request $request, $identity): Response
    {
        $definition = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');

        $remover = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceRemover();
        $remover(
            $definition,
            $identity = new Identity($identity)
        );

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                ($this->buildHeader)($request, $definition, $identity)
            ),
            new StringStream('')
        );
    }
}
