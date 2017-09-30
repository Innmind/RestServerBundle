<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Controller\Resource;

use Innmind\Rest\ServerBundle\Translator\LinkTranslator;
use Innmind\Rest\Server\{
    Response\HeaderBuilder\LinkBuilder,
    Gateway,
    Reference,
    Identity\Identity
};
use Innmind\Http\{
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
    Exception\Http\BadRequest
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    MapInterface,
    Map
};
use Symfony\Component\HttpFoundation\Request;

final class LinkController
{
    private $gateways;
    private $buildHeader;
    private $translator;

    public function __construct(
        MapInterface $gateways,
        LinkBuilder $headerBuilder,
        LinkTranslator $translator
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
        $this->translator = $translator;
    }

    public function defaultAction(Request $request, $identity): Response
    {
        $from = $request->attributes->get('_innmind_resource_definition');
        $request = $request->attributes->get('_innmind_request');

        if (!$request->headers()->has('Link')) {
            throw new BadRequest;
        }

        $tos = $this->translator->translate($request->headers()->get('Link'));

        $linker = $this
            ->gateways
            ->get((string) $from->gateway())
            ->resourceLinker();
        $linker(
            $from = new Reference($from, new Identity($identity)),
            $tos
        );

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                ($this->buildHeader)($request, $from, $tos)
            ),
            new StringStream('')
        );
    }
}
