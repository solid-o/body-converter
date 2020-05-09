<?php

declare(strict_types=1);

namespace Solido\BodyConverter;

use Solido\BodyConverter\Decoder\DecoderProviderInterface;
use Solido\BodyConverter\Exception\UnsupportedFormatException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use function assert;
use function in_array;
use function is_string;

class BodyConverter
{
    private DecoderProviderInterface $decoderProvider;

    public function __construct(DecoderProviderInterface $decoderProvider)
    {
        $this->decoderProvider = $decoderProvider;
    }

    public function decode(Request $request): ParameterBag
    {
        $contentType = $request->headers->get('Content-Type', 'application/x-www-form-urlencoded');
        if ($contentType === null || in_array($request->getMethod(), [Request::METHOD_GET, Request::METHOD_HEAD], true)) {
            return clone $request->request;
        }

        $format = $this->getFormat($request, $contentType);
        if ($format === null || $format === 'form') {
            return clone $request->request;
        }

        try {
            $decoder = $this->decoderProvider->get($format);
        } catch (UnsupportedFormatException $ex) {
            return new ParameterBag();
        }

        $content = $request->getContent();
        assert(is_string($content));

        $parameters = $decoder->decode($content);

        return new ParameterBag($parameters);
    }

    private function getFormat(Request $request, string $contentType): ?string
    {
        $format = $request->getFormat($contentType);
        if ($format === null && $contentType === 'application/merge-patch+json') {
            return 'json';
        }

        return $format;
    }
}
