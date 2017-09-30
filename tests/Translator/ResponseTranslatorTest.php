<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Translator;

use Innmind\Rest\ServerBundle\Translator\ResponseTranslator;
use Innmind\Http\{
    Message\Response\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
    Header,
    Header\ContentType,
    Header\ContentTypeValue,
    Header\Date,
    Header\DateValue,
    TimeContinuum\Format\Http
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\TimeContinuum\PointInTime\Earth\Now;
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
                (new Map('string', Header::class))
                    ->put(
                        'content-type',
                        new ContentType(
                            new ContentTypeValue(
                                'application',
                                'json'
                            )
                        )
                    )
                    ->put(
                        'date',
                        new Date(new DateValue($now = new Now))
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
                'date' => [$now->format(new Http)],
                'cache-control' => ['no-cache, private'],
            ],
            $sfResponse->headers->all()
        );
        $this->assertSame($sfResponse, $translator->translate($response));
    }
}
