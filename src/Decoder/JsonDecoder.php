<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Decoder;

use Solido\BodyConverter\Exception\DecodeException;
use Throwable;
use function is_string;
use function json_decode;
use function Safe\array_walk_recursive;
use const JSON_THROW_ON_ERROR;

class JsonDecoder implements DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function decode(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        try {
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new DecodeException('Invalid request body', 0, $e);
        }

        array_walk_recursive($content, static function (&$value) {
            if ($value === false) {
                $value = '0';
            } elseif ($value !== null && ! is_string($value)) {
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
