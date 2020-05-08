<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Tests\Decoder;

use PHPUnit\Framework\TestCase;
use Solido\BodyConverter\Decoder\JsonDecoder;

class JsonDecoderTest extends TestCase
{
    private JsonDecoder $decoder;

    public function setUp(): void
    {
        $this->decoder = new JsonDecoder();
    }

    public function dataProviderForDecode(): iterable
    {
        return [
            [[], ''],
            [['option' => 0], '{ "option": false }'],
            [['option' => 1], '{ "option": true }'],
            [['options' => ['option' => 0]], '{ "options": { "option": false } }'],
            [['options' => ['option' => 'foobar']], '{ "options": { "option": "foobar" } }'],
        ];
    }

    /**
     * @dataProvider dataProviderForDecode
     */
    public function testDecode(array $expected, string $input): void
    {
        self::assertEquals($expected, $this->decoder->decode($input));
    }
}
