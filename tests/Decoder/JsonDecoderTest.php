<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Tests\Decoder;

use PHPUnit\Framework\TestCase;
use Solido\BodyConverter\Decoder\JsonDecoder;
use Solido\BodyConverter\Exception\InvalidJSONException;

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
            [['option' => '0'], '{ "option": false }'],
            [['option' => '1'], '{ "option": true }'],
            [['option' => '42'], '{ "option": 42 }'],
            [['option' => '12.0435'], '{ "option": 12.0435 }'],
            [['option' => null], '{ "option": null }'],
            [['options' => ['option' => '0']], '{ "options": { "option": false } }'],
            [['options' => ['option' => 'foobar']], '{ "options": { "option": "foobar" } }'],
            [['0', '1', null, '42', 'str'], '[false, true, null, 42, "str"]'],
        ];
    }

    /**
     * @dataProvider dataProviderForDecode
     */
    public function testDecode(array $expected, string $input): void
    {
        self::assertSame($expected, $this->decoder->decode($input));
    }

    public function testDecodeShouldThrowOnInvalidJSON(): void
    {
        $this->expectException(InvalidJSONException::class);
        $this->expectExceptionMessage('Cannot decode JSON: Control character error, possibly incorrectly encoded');
        $this->expectExceptionCode(0);

        try {
            $this->decoder->decode('{ "a": "b');
        } catch (InvalidJSONException $e) {
            self::assertEquals('{ "a": "b', $e->getInvalidJson());
            throw $e;
        }
    }
}
