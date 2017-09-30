<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Request\Verifier;

use Innmind\Rest\Server\{
    Request\Verifier\Verifier,
    Definition\HttpResource
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
    Exception\Http\BadRequest,
    Header\LinkValue
};
use Symfony\Component\Routing\RouterInterface;

final class LinkVerifier implements Verifier
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     *
     * @throws BadRequest
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition
    ): void {
        if (
            (string) $request->method() !== Method::LINK &&
            (string) $request->method() !== Method::UNLINK
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
                    throw new BadRequest;
                }

                $path = $infos['_innmind_resource'];

                if (
                    !$definition->allowedLinks()->contains($link->relationship()) ||
                    $definition->allowedLinks()->get($link->relationship()) !== $path
                ) {
                    throw new BadRequest;
                }
            });
    }
}
