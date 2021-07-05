<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Adapter;

use Symfony\Component\HttpFoundation\Request;

use function assert;
use function is_string;

class HttpFoundationAdapter implements AdapterInterface
{
    public function getContentType(object $request): string
    {
        assert($request instanceof Request);

        $contentType = $request->headers->get('Content-Type', 'application/x-www-form-urlencoded');
        assert(is_string($contentType));

        return $contentType;
    }

    public function getRequestMethod(object $request): string
    {
        assert($request instanceof Request);

        return $request->getMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestParams(object $request): array
    {
        assert($request instanceof Request);

        return $request->request->all();
    }

    public function getRequestContent(object $request): string
    {
        assert($request instanceof Request);

        $content = $request->getContent(false);
        assert(is_string($content));

        return $content;
    }
}
