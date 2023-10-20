<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Decoder;

use Solido\BodyConverter\Exception\InvalidJSONException;
use Throwable;

use function array_walk_recursive;
use function assert;
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
        if (empty($content)) {
            return [];
        }

        try {
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new InvalidJSONException($content, $e->getMessage(), $e);
        }

        assert(is_array($content));
        array_walk_recursive($content, static function (&$value): void {
            if ($value === false) {
                $value = '0';
            } elseif ($value !== null) {
                $value = (string) $value;
            }
        });

        return $content;
    }

    public static function getFormat(): string
    {
        return 'json';
    }
}
