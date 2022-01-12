<?php

declare(strict_types=1);

namespace Solido\BodyConverter;

use Solido\BodyConverter\Decoder\DecoderProvider;
use Solido\BodyConverter\Decoder\DecoderProviderInterface;
use Solido\BodyConverter\Decoder\FormDecoder;
use Solido\BodyConverter\Decoder\JsonDecoder;
use Solido\Common\AdapterFactory;
use Solido\Common\AdapterFactoryInterface;

final class BodyConverter implements BodyConverterInterface
{
    use BodyConverterTrait;

    private const FORM_FORMAT = 'application/x-www-form-urlencoded';
    private const JSON_FORMATS = [
        'application/json' => true,
        'application/x-json' => true,
        'application/ld+json' => true,
        'application/json-patch+json' => true,
        'application/merge-patch+json' => true,
    ];

    public function __construct(?DecoderProviderInterface $decoderProvider = null, ?AdapterFactoryInterface $adapterFactory = null)
    {
        $this->adapterFactory = $adapterFactory ?? new AdapterFactory();
        $this->decoderProvider = $decoderProvider ?? new DecoderProvider([
            'form' => new FormDecoder(),
            'json' => new JsonDecoder(),
        ]);
    }

    private function getFormat(string $contentType): ?string
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
