<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Decoder;

interface DecoderInterface
{
    /**
     * Decode a request content and make a application/x-www-form-encoded compliant values.
     *
     * @return array<mixed, mixed>
     */
    public function decode(string $content): array;

    /**
     * Get the format supported by this decoder.
     */
    public static function getFormat(): string;
}
