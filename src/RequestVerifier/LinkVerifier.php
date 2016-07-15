<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\RequestVerifier;

use Innmind\Rest\Server\{
    RequestVerifier\VerifierInterface,
    Definition\HttpResource
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Message\MethodInterface,
    Exception\Http\BadRequestException,
    Header\LinkValue
};
use Symfony\Component\Routing\RouterInterface;

final class LinkVerifier implements VerifierInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     *
     * @throws BadRequestException
     */
    public function verify(
        ServerRequestInterface $request,
        HttpResource $definition
    ) {
        if (
            (string) $request->method() !== MethodInterface::LINK &&
            (string) $request->method() !== MethodInterface::UNLINK
        ) {
            return;
        }

        $request
            ->headers()
            ->get('Link')
            ->values()
            ->foreach(function(LinkValue $link) use ($definition) {
                $infos = $this->router->match((string) $link->url());

                if (
                    !isset($infos['_innmind_resource']) ||
                    !isset($infos['identity'])
                ) {
                    throw new BadRequestException;
                }

                $path = $infos['_innmind_resource'];

                if (
                    !$definition->allowedLinks()->contains($path) ||
                    $definition->allowedLinks()->get($path) !== $link->relationship()
                ) {
                    throw new BadRequestException;
                }
            });
    }
}
