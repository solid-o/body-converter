<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Exception;

use Throwable;

use function sprintf;

class InvalidJSONException extends DecodeException
{
    public function __construct(private string $invalidJson, string $error, Throwable|null $previous = null)
    {
        parent::__construct(sprintf('Cannot decode JSON: %s', $error), 0, $previous);
    }

    /**
     * Gets the invalid json.
     */
    public function getInvalidJson(): string
    {
        return $this->invalidJson;
    }
}
