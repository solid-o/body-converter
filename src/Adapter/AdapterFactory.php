<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Adapter;

use Psr\Http\Message\RequestInterface;
use Solido\BodyConverter\Exception\UnsupportedRequestObjectException;
use Symfony\Component\HttpFoundation\Request;

use function get_class;
use function Safe\sprintf;

class AdapterFactory implements AdapterFactoryInterface
{
    public function factory(object $request): AdapterInterface
    {
        if ($request instanceof Request) {
            return new HttpFoundationAdapter();
        }

        if ($request instanceof RequestInterface) {
            return new ServerRequestAdapter();
        }

        throw new UnsupportedRequestObjectException(
            sprintf('Cannot create an adapter for the request class "%s"', get_class($request))
        );
    }
}
