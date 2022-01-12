<?php

declare(strict_types=1);

namespace Solido\BodyConverter;

interface BodyConverterInterface
{
    /**
     * Decodes request content into an array of parameters.
     *
     * @return array<int|string, mixed>
     */
    public function decode(object $request): array;
}
