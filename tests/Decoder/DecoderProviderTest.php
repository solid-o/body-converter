<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Tests\Decoder;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Solido\BodyConverter\Decoder\DecoderInterface;
use Solido\BodyConverter\Decoder\DecoderProvider;
use Solido\BodyConverter\Exception\UnsupportedFormatException;

class DecoderProviderTest extends TestCase
{
    use ProphecyTrait;

    public function getProviders(): iterable
    {
        return [
            'json' => $this->prophesize(DecoderInterface::class)->reveal(),
        ];
    }

    public function testSupportShouldReturnFalseIfFormatIsNotSupported(): void
    {
        $provider = new DecoderProvider($this->getProviders());

        self::assertFalse($provider->supports('xml'));
    }

    public function testSupportShouldReturnTrueIfFormatIsSupported(): void
    {
        $provider = new DecoderProvider($this->getProviders());

        self::assertTrue($provider->supports('json'));
    }

    public function testGetShouldThrowIfFormatIsNotSupported(): void
    {
        $this->expectException(UnsupportedFormatException::class);
        $provider = new DecoderProvider($this->getProviders());

        $provider->get('xml');
    }

    public function testGetShouldNotThrowIfFormatIsSupported(): void
    {
        $provider = new DecoderProvider($this->getProviders());

        self::assertNotNull($provider->get('json'));
    }
}
