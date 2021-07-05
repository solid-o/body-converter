<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Adapter;

interface AdapterInterface
{
    /**
     * Gets the content type from the given request object.
     */
    public function getContentType(object $request): string;

    /**
     * Gets the request method.
     */
    public function getRequestMethod(object $request): string;

    /**
     * Gets the server parameters from the request object.
     *
     * @return array<string, mixed>
     */
    public function getRequestParams(object $request): array;

    /**
     * Gets the request content as string.
     */
    public function getRequestContent(object $request): string;
}
