<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Translator;

use Innmind\Http\{
    Message\ServerRequestInterface,
    Message\ServerRequest,
    Message\Method,
    Message\Environment,
    Message\EnvironmentInterface,
    Message\Cookies,
    Message\CookiesInterface,
    Message\Query,
    Message\QueryInterface,
    Message\Query\ParameterInterface as QueryParameterInterface,
    Message\Query\Parameter as QueryParameter,
    Message\Form,
    Message\FormInterface,
    Message\Form\ParameterInterface as FormParameterInterface,
    Message\Form\Parameter as FormParameter,
    Message\Files,
    Message\FilesInterface,
    File\File,
    File\FileInterface,
    File\StatusInterface,
    File\OkStatus,
    File\ExceedsFormMaxFileSizeStatus,
    File\ExceedsIniMaxFileSizeStatus,
    File\NoTemporaryDirectoryStatus,
    File\NotUploadedStatus,
    File\PartiallyUploadedStatus,
    File\StoppedByExtensionStatus,
    File\WriteFailedStatus,
    ProtocolVersion,
    Headers,
    HeadersInterface,
    Header\HeaderInterface,
    Factory\HeaderFactoryInterface
};
use Innmind\Filesystem\{
    Stream\Stream,
    MediaType\MediaType
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Map,
    StringPrimitive as Str
};
use Symfony\Component\HttpFoundation\{
    Request,
    HeaderBag,
    ParameterBag,
    FileBag,
    ServerBag
};

final class RequestTranslator
{
    private $transformed;
    private $headerFactory;

    public function __construct(HeaderFactoryInterface $headerFactory)
    {
        $this->transformed = new Map(
            Request::class,
            ServerRequestInterface::class
        );
        $this->headerFactory = $headerFactory;
    }

    public function translate(Request $request): ServerRequestInterface
    {
        if ($this->transformed->contains($request)) {
            return $this->transformed->get($request);
        }

        $serverRequest = $this->doTranslation($request);
        $this->transformed = $this->transformed->put(
            $request,
            $serverRequest
        );

        return $serverRequest;
    }

    private function doTranslation(Request $request): ServerRequestInterface
    {
        $protocol = (new Str($request->server->get('SERVER_PROTOCOL')))->getMatches(
            '~HTTP/(?<major>\d)\.(?<minor>\d)~'
        );

        return new ServerRequest(
            Url::fromString(
                $request->getSchemeAndHttpHost().'/'.ltrim($request->getPathInfo(), '/')
            ),
            new Method($request->getMethod()),
            new ProtocolVersion(
                (int) (string) $protocol['major'],
                (int) (string) $protocol['minor']
            ),
            $this->translateHeaders($request->headers),
            new Stream($request->getContent(true)),
            $this->translateEnvironment($request->server),
            $this->translateCookies($request->cookies),
            $this->translateQuery($request->query),
            $this->translateForm($request->request),
            $this->translateFiles($request->files)
        );
    }

    private function translateHeaders(HeaderBag $headerBag): HeadersInterface
    {
        $map = new Map('string', HeaderInterface::class);

        foreach ($headerBag as $name => $value) {
            $map = $map->put(
                $name,
                $this->headerFactory->make(
                    new Str($name),
                    new Str(implode(', ', $value))
                )
            );
        }

        return new Headers($map);
    }

    private function translateEnvironment(ServerBag $server): EnvironmentInterface
    {
        $map = new Map('string', 'scalar');

        foreach ($server as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $map = $map->put($key, $value);
        }

        return new Environment($map);
    }

    private function translateCookies(ParameterBag $cookies): CookiesInterface
    {
        $map = new Map('string', 'scalar');

        foreach ($cookies as $key => $value) {
            $map = $map->put($key, $value);
        }

        return new Cookies($map);
    }

    private function translateQuery(ParameterBag $query): QueryInterface
    {
        $map = new Map('string', QueryParameterInterface::class);

        foreach ($query as $key => $value) {
            $map = $map->put(
                $key,
                new QueryParameter($key, $value)
            );
        }

        return new Query($map);
    }

    private function translateForm(ParameterBag $form): FormInterface
    {
        $map = new Map('scalar', FormParameterInterface::class);

        foreach ($form as $key => $value) {
            $map = $map->put(
                $key,
                $this->buildFormParameter($key, $value)
            );
        }

        return new Form($map);
    }

    private function buildFormParameter($name, $value): FormParameterInterface
    {
        if (!is_array($value)) {
            return new FormParameter((string) $name, $value);
        }

        $map = new Map('scalar', FormParameterInterface::class);

        foreach ($value as $key => $sub) {
            $map = $map->put(
                $key,
                $this->buildFormParameter($key, $sub)
            );
        }

        return new FormParameter((string) $name, $map);
    }

    private function translateFiles(FileBag $files): FilesInterface
    {
        $map = new Map('string', FileInterface::class);

        foreach ($files as $name => $file) {
            $map = $map->put(
                $name,
                new File(
                    $file->getClientOriginalName(),
                    new Stream(
                        fopen($file->getPathname(), 'r')
                    ),
                    $this->buildFileStatus($file->getError()),
                    MediaType::fromString((string) $file->getClientMimeType())
                )
            );
        }

        return new Files($map);
    }

    private function buildFileStatus(int $status): StatusInterface
    {
        switch ($status) {
            case UPLOAD_ERR_FORM_SIZE:
                return new ExceedsFormMaxFileSizeStatus;
            case UPLOAD_ERR_INI_SIZE:
                return new ExceedsIniMaxFileSizeStatus;
            case UPLOAD_ERR_NO_TMP_DIR:
                return new NoTemporaryDirectoryStatus;
            case UPLOAD_ERR_NO_FILE:
                return new NotUploadedStatus;
            case UPLOAD_ERR_OK:
                return new OkStatus;
            case UPLOAD_ERR_PARTIAL:
                return new PartiallyUploadedStatus;
            case UPLOAD_ERR_EXTENSION:
                return new StoppedByExtensionStatus;
            case UPLOAD_ERR_CANT_WRITE:
                return new WriteFailedStatus;
        }
    }
}
