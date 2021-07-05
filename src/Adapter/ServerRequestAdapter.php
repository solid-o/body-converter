<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Adapter;

use Psr\Http\Message\ServerRequestInterface;

use function assert;
use function get_object_vars;
use function is_object;

class ServerRequestAdapter implements AdapterInterface
{
    public function getContentType(object $request): string
    {
        assert($request instanceof ServerRequestInterface);
        $header = $request->getHeader('Content-Type');

        return $header[0] ?? 'application/x-www-form-urlencoded';
    }

    public function getRequestMethod(object $request): string
    {
        assert($request instanceof ServerRequestInterface);

        return $request->getMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestParams(object $request): array
    {
        assert($request instanceof ServerRequestInterface);

        $parsedBody = $request->getParsedBody();
        if ($parsedBody === null) {
            return [];
        }

        if (is_object($parsedBody)) {
            return get_object_vars($parsedBody);
        }

        return $parsedBody;
    }

    public function getRequestContent(object $request): string
    {
        assert($request instanceof ServerRequestInterface);

        return (string) $request->getBody();
    }
}
