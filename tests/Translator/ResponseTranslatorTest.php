<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Translator;

use Innmind\Rest\ServerBundle\Translator\ResponseTranslator;
use Innmind\Http\{
    Message\Response,
    Message\StatusCode,
    Message\ReasonPhrase,
    ProtocolVersion,
    Headers,
    Header\HeaderInterface,
    Header\ContentType,
    Header\ContentTypeValue,
    Header\ParameterInterface
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\Map;
use Symfony\Component\HttpFoundation\Response as SfResponse;
use PHPUnit\Framework\TestCase;

class ResponseTranslatorTest extends TestCase
{
    public function testTranslate()
    {
        $translator = new ResponseTranslator;

        $response = new Response(
            new StatusCode(200),
            new ReasonPhrase('OK'),
            new ProtocolVersion(1, 1),
            new Headers(
                (new Map('string', HeaderInterface::class))
                    ->put(
                        'content-type',
                        new ContentType(
                            new ContentTypeValue(
                                'application',
                                'json',
                                new Map('string', ParameterInterface::class)
                            )
                        )
                    )
            ),
            new StringStream('{"answer":42}')
        );

        $sfResponse = $translator->translate($response);

        $this->assertInstanceOf(SfResponse::class, $sfResponse);
        $this->assertSame(200, $sfResponse->getStatusCode());
        $this->assertSame('1.1', $sfResponse->getProtocolVersion());
        $this->assertSame('{"answer":42}', $sfResponse->getContent());
        $this->assertSame(
            [
                'content-type' => ['application/json'],
                'cache-control' => ['no-cache, private'],
            ],
            $sfResponse->headers->all()
        );
        $this->assertSame($sfResponse, $translator->translate($response));
    }
}
