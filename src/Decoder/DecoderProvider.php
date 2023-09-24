<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Decoder;

use Solido\BodyConverter\Exception\UnsupportedFormatException;

use function Safe\sprintf;

class DecoderProvider implements DecoderProviderInterface
{
    /** @param array<string, DecoderInterface> $decoders */
    public function __construct(private array $decoders)
    {
    }

    public function get(string $format): DecoderInterface
    {
        if (! isset($this->decoders[$format])) {
            throw new UnsupportedFormatException(sprintf('Format %s is not supported', $format));
        }

        return $this->decoders[$format];
    }

    public function supports(string $format): bool
    {
        return isset($this->decoders[$format]);
    }
}
