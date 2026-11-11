<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Tests\Decoder;

use PHPUnit\Framework\Attributes\DataProvider;
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

    public static function dataProviderForDecode(): iterable
    {
        return [
            [[], ''],
            [[], '{}'],
            [[], '[]'],
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

    #[DataProvider('dataProviderForDecode')]
    public function testDecode(array $expected, string $input): void
    {
        self::assertSame($expected, $this->decoder->decode($input));
    }

    public static function scalarJsonProvider(): iterable
    {
        return [
            'zero' => ['0'],
            'integer' => ['42'],
            'negative integer' => ['-42'],
            'float' => ['12.0435'],
            'true' => ['true'],
            'false' => ['false'],
            'null' => ['null'],
            'string' => ['"test"'],
            'empty string' => ['""'],
            'surrounding whitespace' => [" \n false \t"],
        ];
    }

    #[DataProvider('scalarJsonProvider')]
    public function testScalarJsonProducesControlledDecodeError(string $input): void
    {
        $this->expectException(InvalidJSONException::class);
        $this->expectExceptionMessage('Cannot decode JSON: Expected a JSON object or array.');

        try {
            $this->decoder->decode($input);
        } catch (InvalidJSONException $exception) {
            self::assertSame($input, $exception->getInvalidJson());
            throw $exception;
        }
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
