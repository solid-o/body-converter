<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Adapter;

interface AdapterFactoryInterface
{
    /**
     * Creates an adapter for the given request.
     */
    public function factory(object $request): AdapterInterface;
}
