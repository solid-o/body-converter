<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Decoder;

use function count;
use function end;
use function explode;
use function is_array;
use function key;
use function strlen;
use function strpbrk;
use function strpos;
use function strstr;
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

    /**
     * @return array<array-key, mixed>
     */
    private static function queryStringToArray(string $queryString): array
    {
        $queryString = trim($queryString);
        $firstChar = $queryString[0] ?? '';
        if ($firstChar === '?' || $firstChar === '#' || $firstChar === '&') {
            $queryString = substr($queryString, 1);
        }

        if (empty($queryString)) {
            return [];
        }

        $parameters = [];
        // Process one parameter at a time, split query string by "&"
        foreach (explode('&', $queryString) as $parameter) {
            if ($parameter === '') {
                // Empty parameter, ignore.
                continue;
            }

            // Search for an equal sign; if not present, the value is null.
            [$key, $value] = explode('=', $parameter, 2) + [null, null];

            // Decode parameters: will be sent encoded from the browser.
            $key = urldecode($key);
            $value = urldecode($value ?? '');

            $pos = strpos($key, '[');
            if ($pos === false) {
                // No need to process it further.
                $parameters[$key] = $value;
                continue;
            }

            // Check if parameter key is well-formed
            $i = strpos($key, '[');
            $token = substr($key, 0, $i);
            $tokens = [];

            $key = strpbrk($key, '[');
            for (; $key !== '';) {
                if ($key[0] !== '[') {
                    break;
                }

                $key = substr($key, 1);
                $n = strstr($key, ']', true);
                if ($n === false) {
                    $tokens[] = $token . (empty($tokens) ? '[' . $key : '');
                    $token = null;
                    break;
                }

                $tokens[] = $token;
                $token = $n;
                $key = substr($key, strlen($n) + 1);
            }

            if ($token !== null) {
                $tokens[] = $token;
            }

            $current = &$parameters;
            $len = count($tokens);
            foreach ($tokens as $idx => $token) {
                if ($idx === $len - 1) {
                    // We reached the end of the tokens list. Now add the parameter
                    // value to the appropriate key (push to the array if no key is specified)
                    if ($token === '') {
                        $current[] = $value;
                    } else {
                        $current[$token] = $value;
                    }

                    break;
                }

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
        }

        return $parameters;
    }
}
