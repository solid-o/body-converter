<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Exception;

use Throwable;

use function Safe\sprintf;

class InvalidJSONException extends DecodeException
{
    private string $invalidJson;

    public function __construct(string $invalidJson, string $error, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Cannot decode JSON: %s', $error), 0, $previous);

        $this->invalidJson = $invalidJson;
    }

    /**
     * Gets the invalid json.
     */
    public function getInvalidJson(): string
    {
        return $this->invalidJson;
    }
}
