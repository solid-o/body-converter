<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Decoder;

use function array_pop;
use function assert;
use function count;
use function end;
use function explode;
use function is_array;
use function key;
use function strpbrk;
use function strpos;
use function strtok;
use function substr;
use function trim;
use function urldecode;

class FormDecoder implements DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function decode(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        return self::queryStringToArray($content);
    }

    public static function getFormat(): string
    {
        return 'form';
    }

    /** @return array<array-key, mixed> */
    private static function queryStringToArray(string $queryString): array
    {
        $queryString = trim($queryString);
        $firstChar = $queryString[0] ?? '';
        if ($firstChar === '?' || $firstChar === '#' || $firstChar === '&') {
            $queryString = substr($queryString, 1); /* @phpstan-ignore-line */
        }

        if (empty($queryString)) {
            return [];
        }

        $parameters = [];
        // Process one parameter at a time, split query string by "&"
        $parameter = strtok($queryString, '&');
        for (; $parameter !== false; $parameter = strtok('&')) {
            // Search for an equal sign; if not present, the value is null.
            [$key, $value] = explode('=', $parameter, 2) + ['', ''];

            // Decode parameters: will be sent encoded from the browser.
            $key = urldecode($key);
            $value = urldecode($value);

            $pos = strpos($key, '[');
            if ($pos === false) {
                // No need to process it further.
                $parameters[$key] = $value;
                continue;
            }

            // Check if parameter key is well-formed
            $token = substr($key, 0, $pos); /* @phpstan-ignore-line */
            $tokens = [];

            $key = strpbrk($key, '[');
            assert($key !== false);

            while ($key !== '' && $key[0] === '[') {
                $key = substr($key, 1); /* @phpstan-ignore-line */
                $pos = strpos($key, ']');
                if ($pos === false) {
                    // ']' character cannot be found in the key which means that the parameter is malformed
                    // If the token set is empty use the entire key, otherwise use only the already collected
                    // tokens and discard the rest of the key.
                    $tokens[] = $token . (count($tokens) === 0 ? "[$key" : ''); // phpcs:disable Squiz.Strings.DoubleQuoteUsage.ContainsVar
                    $token = null;
                    break;
                }

                $tokens[] = $token;
                $token = substr($key, 0, $pos); /* @phpstan-ignore-line */
                $key = substr($key, $pos + 1); /* @phpstan-ignore-line */
            }

            if ($token !== null) {
                $tokens[] = $token;
            }

            $current = &$parameters;
            $lastToken = array_pop($tokens);
            foreach ($tokens as $token) {
                // Here's where the magic happens: current is now a pointer to the current-nesting level
                // parameter array. We know that this is not the last token, so we have to create
                // empty arrays if needed, and prepare for the next loop.
                if ($token === '') {
                    // No key, push a new element into the array.
                    $current[] = [];
                    end($current);
                    $nextKey = key($current);
                } else {
                    // Prepare an empty array if needed
                    if (! isset($current[$token])) {
                        $current[$token] = [];
                    }

                    $nextKey = $token;
                }

                if (! is_array($current[$nextKey])) {
                    $current[$nextKey] = [];
                }

                // Update the current pointer.
                $current = &$current[$nextKey];
            }

            // We reached the end of the tokens list. Now add the parameter
            // value to the appropriate key (push to the array if no key is specified)
            if ($lastToken === '') {
                $current[] = $value;
            } else {
                $current[$lastToken] = $value;
            }
        }

        return $parameters;
    }
}
