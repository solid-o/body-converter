<?php

declare(strict_types=1);

namespace Solido\BodyConverter;

use Solido\BodyConverter\Decoder\DecoderProviderInterface;
use Solido\Common\AdapterFactoryInterface;

use function in_array;

trait BodyConverterTrait
{
    private DecoderProviderInterface $decoderProvider;
    private AdapterFactoryInterface $adapterFactory;

    /**
     * {@inheritdoc}
     */
    public function decode(object $request): array
    {
        $adapter = $this->adapterFactory->createRequestAdapter($request);
        $contentType = $adapter->getContentType();
        if (
            empty($contentType) ||
            in_array($adapter->getRequestMethod(), ['GET', 'HEAD'], true)
        ) {
            return $adapter->getRequestParams();
        }

        $format = $this->getFormat($contentType);
        if ($format === null) {
            return $adapter->getRequestParams();
        }

        if (! $this->decoderProvider->supports($format)) {
            return [];
        }

        $decoder = $this->decoderProvider->get($format);
        $content = $adapter->getRequestContent();

        return $decoder->decode($content);
    }

    abstract protected function getFormat(string $contentType): string|null;
}
