<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Decoder;

use Solido\BodyConverter\Exception\InvalidJSONException;
use Throwable;

use function array_walk_recursive;
use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class JsonDecoder implements DecoderInterface
{
    /**
     * {@inheritDoc}
     */
    public function decode(string $content): array
    {
        if ($content === '') {
            return [];
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new InvalidJSONException($content, $e->getMessage(), $e);
        }

        if (! is_array($decoded)) {
            throw new InvalidJSONException($content, 'Expected a JSON object or array.');
        }

        array_walk_recursive($decoded, static function (&$value): void {
            if ($value === false) {
                $value = '0';
            } elseif ($value !== null) {
                $value = (string) $value;
            }
        });

        return $decoded;
    }

    public static function getFormat(): string
    {
        return 'json';
    }
}
