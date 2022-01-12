<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Tests\Decoder;

use PHPUnit\Framework\TestCase;
use Solido\BodyConverter\Decoder\FormDecoder;
use Solido\BodyConverter\Decoder\JsonDecoder;

class FormDecoderTest extends TestCase
{
    private FormDecoder $decoder;

    public function setUp(): void
    {
        $this->decoder = new FormDecoder();
    }

    public function dataProviderForDecode(): iterable
    {
        return [
            [[], ''],
            [['foo' => 'bar'], 'foo=bar'],
            [['foo' => 'bar'], '   foo=bar  '],
            [['foo' => 'bar'], '?foo=bar'],
            [['foo' => 'bar'], '#foo=bar'],
            [['foo' => 'bar'], '&foo=bar'],
            [['foo' => 'bar', 'bar' => 'foo'], 'foo=bar&bar=foo'],
            [['foo' => 'bar', 'bar' => 'foo'], 'foo=bar&&bar=foo'],
            [['foo' => ['bar' => ['baz' => ['bax' => 'bar']]]], 'foo[bar][baz][bax]=bar'],
            [['foo' => ['bar' => 'bar']], 'foo[bar] [baz]=bar'],
            [['foo' => ['bar' => ['baz' => ['bar', 'foo']]]], 'foo[bar][baz][]=bar&foo[bar][baz][]=foo'],
            [['foo' => ['bar' => [['bar'], ['foo']]]], 'foo[bar][][]=bar&foo[bar][][]=foo'],
            [['option' => ''], 'option'],
            [['option' => '0'], 'option=0'],
            [['option' => '1'], 'option=1'],
            [['foo' => 'bar=bar=='], 'foo=bar=bar=='],
            [['options' => ['option' => '0']], 'options[option]=0'],
            [['options' => ['option' => 'foobar']], 'options[option]=foobar'],
            [['sum' => '10\\2=5'], 'sum=10%5c2%3d5'],

            // Special cases
            [[
                'a' => '<==  foo bar  ==>',
                'b' => '###Hello World###',
            ], 'a=%3c%3d%3d%20%20foo+bar++%3d%3d%3e&b=%23%23%23Hello+World%23%23%23'],
            [['str' => "A string with containing \0\0\0 nulls"], 'str=A%20string%20with%20containing%20%00%00%00%20nulls'],
            [[
                'arr[1' => 'sid',
                'arr' => ['4' => 'fred'],
            ], 'arr[1=sid&arr[4][2=fred'],
            [[
                'arr[1' => 'sid',
                'arr' => ['4' => ['[2' => 'fred']],
            ], 'arr[1=sid&arr[4][[2][3[=fred'],
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
