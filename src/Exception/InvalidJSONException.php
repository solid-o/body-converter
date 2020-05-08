<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Exception;

use RuntimeException;
use function Safe\sprintf;

class InvalidJSONException extends RuntimeException
{
    private string $invalidJson;

    public function __construct(string $invalidJson, string $error)
    {
        parent::__construct(sprintf('Cannot decode JSON: %s', $error));

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
