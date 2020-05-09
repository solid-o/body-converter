<?php

declare(strict_types=1);

namespace Solido\BodyConverter;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

interface BodyConverterInterface
{
    /**
     * Decodes request content into a parameter bag.
     */
    public function decode(Request $request): ParameterBag;
}
