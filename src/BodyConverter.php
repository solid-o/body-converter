<?php

declare(strict_types=1);

namespace Solido\BodyConverter;

use Solido\BodyConverter\Decoder\DecoderProvider;
use Solido\BodyConverter\Decoder\DecoderProviderInterface;
use Solido\BodyConverter\Decoder\JsonDecoder;
use Solido\BodyConverter\Exception\UnsupportedFormatException;
use Solido\Common\RequestAdapter\RequestAdapterFactory;
use Solido\Common\RequestAdapter\RequestAdapterFactoryInterface;

use function in_array;

final class BodyConverter implements BodyConverterInterface
{
    private const FORM_FORMAT = 'application/x-www-form-urlencoded';
    private const JSON_FORMATS = [
        'application/json' => true,
        'application/x-json' => true,
        'application/ld+json' => true,
        'application/json-patch+json' => true,
        'application/merge-patch+json' => true,
    ];

    private DecoderProviderInterface $decoderProvider;
    private RequestAdapterFactoryInterface $adapterFactory;

    public function __construct(?DecoderProviderInterface $decoderProvider = null, ?RequestAdapterFactoryInterface $adapterFactory = null)
    {
        $this->adapterFactory = $adapterFactory ?? new RequestAdapterFactory();
        $this->decoderProvider = $decoderProvider ?? new DecoderProvider([
            'json' => new JsonDecoder(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function decode(object $request): array
    {
        $adapter = $this->adapterFactory->factory($request);
        $contentType = $adapter->getContentType();
        if (
            empty($contentType) ||
            in_array($adapter->getRequestMethod(), ['GET', 'HEAD'], true)
        ) {
            return $adapter->getRequestParams();
        }

        $format = $this->getFormat($contentType);
        if ($format === null || $format === 'form') {
            return $adapter->getRequestParams();
        }

        try {
            $decoder = $this->decoderProvider->get($format);
        } catch (UnsupportedFormatException $ex) {
            return [];
        }

        $content = $adapter->getRequestContent();

        return $decoder->decode($content);
    }

    protected function getFormat(string $contentType): ?string
    {
        if (isset(self::JSON_FORMATS[$contentType])) {
            return 'json';
        }

        if ($contentType === self::FORM_FORMAT) {
            return 'form';
        }

        return null;
    }
}
