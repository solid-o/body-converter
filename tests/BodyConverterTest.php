<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Tests;

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

        $this->converter->decode($request);

        self::assertEquals(['options' => ['option' => '0']], $request->request->all());
    }
}
