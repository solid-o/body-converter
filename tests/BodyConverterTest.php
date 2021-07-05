<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Tests;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Solido\BodyConverter\BodyConverter;
use Solido\BodyConverter\Decoder\DecoderProvider;
use Solido\BodyConverter\Decoder\JsonDecoder;
use Symfony\Component\HttpFoundation\Request;

class BodyConverterTest extends TestCase
{
    private BodyConverter $converter;

    protected function setUp(): void
    {
        $provider = new DecoderProvider(['json' => new JsonDecoder()]);
        $this->converter = new BodyConverter($provider);
    }

    public function testShouldDecodeContentCorrectly(): void
    {
        $request = new Request([], [], [], [], [], [], '{ "options": { "option": false } }');
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('Content-Type', 'application/json');

        self::assertEquals(['options' => ['option' => '0']], $this->converter->decode($request));
    }

    public function testShouldDecodeContentCorrectlyFromPsrServerRequest(): void
    {
        $request = new ServerRequest(
            'POST',
            'http://localhost/',
            ['Content-Type' => 'application/json'],
            '{ "options": { "option": false } }'
        );

        self::assertEquals(['options' => ['option' => '0']], $this->converter->decode($request));
    }

    public function testShouldReturnACopyOfOriginalParameterBagIfFormDataHasBeenPassed(): void
    {
        $request = new Request([], ['options' => ['option' => '0']]);
        $request->setMethod(Request::METHOD_POST);

        $ret = $this->converter->decode($request);

        self::assertEquals(['options' => ['option' => '0']], $ret);
    }

    public function testShouldReturnACopyOfParsedBodyIfFormDataHasBeenPassed(): void
    {
        $request = (new ServerRequest('POST', 'http://localhost/'))
            ->withParsedBody(['options' => ['option' => '0']]);

        $ret = $this->converter->decode($request);

        self::assertEquals(['options' => ['option' => '0']], $ret);
    }
}
