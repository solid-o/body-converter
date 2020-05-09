<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Tests;

use PHPUnit\Framework\TestCase;
use Solido\BodyConverter\BodyConverter;
use Solido\BodyConverter\Decoder\DecoderProvider;
use Solido\BodyConverter\Decoder\JsonDecoder;
use Symfony\Component\HttpFoundation\Request;
use function spl_object_hash;

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

        self::assertEquals(['options' => ['option' => '0']], $this->converter->decode($request)->all());
    }

    public function testShouldReturnACopyOfOriginalParameterBagIfFormDataHasBeenPassed(): void
    {
        $request = new Request([], ['options' => ['option' => '0']]);
        $request->setMethod(Request::METHOD_POST);

        $ret = $this->converter->decode($request);

        self::assertEquals(['options' => ['option' => '0']], $ret->all());
        self::assertNotEquals(spl_object_hash($ret), spl_object_hash($request->request));
    }
}
