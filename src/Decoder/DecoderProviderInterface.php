<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Decoder;

use Solido\BodyConverter\Exception\UnsupportedFormatException;

interface DecoderProviderInterface
{
    /**
     * Get a corresponding decoder from format.
     *
     * @throws UnsupportedFormatException
     */
    public function get(string $format): DecoderInterface;

    /**
     * Check if there's a decoder supporting the format.
     */
    public function supports(string $format): bool;
}
