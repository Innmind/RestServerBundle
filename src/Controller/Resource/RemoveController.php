<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Exception\InvalidArgumentException;
use Innmind\Rest\Server\{
    Response\HeaderBuilder\RemoveBuilderInterface,
    GatewayInterface,
    Identity
};
use Innmind\Http\{
    Message\ResponseInterface,
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    Headers
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\MapInterface;
use Symfony\Component\HttpFoundation\Request;

final class RemoveController
{
    private $gateways;
    private $headerBuilder;

    public function __construct(
        MapInterface $gateways,
        RemoveBuilderInterface $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== GatewayInterface::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->gateways = $gateways;
        $this->headerBuilder = $headerBuilder;
    }

    public function defaultAction(Request $request, $identity): ResponseInterface
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

        return new Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                $this->headerBuilder->build(
                    $request,
                    $definition,
                    $identity
                )
            ),
            new StringStream('')
        );
    }
}
