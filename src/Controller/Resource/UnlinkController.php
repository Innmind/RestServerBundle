<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\{
    Exception\InvalidArgumentException,
    Translator\LinkTranslator
};
use Innmind\Rest\Server\{
    Response\HeaderBuilder\UnlinkBuilderInterface,
    GatewayInterface,
    Reference,
    Identity,
    Link\ParameterInterface,
    Link\Parameter
};
use Innmind\Http\{
    Message\ResponseInterface,
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    Headers,
    Header\Link,
    Header\LinkValue,
    Header\ParameterInterface as LinkParameterInterface,
    Exception\Http\BadRequestException
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    MapInterface,
    Map
};
use Symfony\Component\HttpFoundation\Request;

final class UnlinkController
{
    private $gateways;
    private $headerBuilder;
    private $translator;

    public function __construct(
        MapInterface $gateways,
        UnlinkBuilderInterface $headerBuilder,
        LinkTranslator $translator
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== GatewayInterface::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->gateways = $gateways;
        $this->headerBuilder = $headerBuilder;
        $this->translator = $translator;
    }

    public function defaultAction(Request $request, $identity): ResponseInterface
    {
        $from = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');

        if (!$request->headers()->has('Link')) {
            throw new BadRequestException;
        }

        $tos = $this->translator->translate($request->headers()->get('Link'));

        $unlinker = $this
            ->gateways
            ->get((string) $from->gateway())
            ->resourceUnlinker();
        $unlinker(
            $from = new Reference($from, new Identity($identity)),
            $tos
        );

        return new Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                $this->headerBuilder->build(
                    $request,
                    $from,
                    $tos
                )
            ),
            new StringStream('')
        );
    }
}
