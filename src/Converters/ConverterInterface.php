<?php

declare(strict_types=1);

namespace ConfigurationConverter\Converters;

interface ConverterInterface
{
    public function convert(string $resourceClass): void;

    /**
     * Can this converter be used for the asked format?
     */
    public function support(string $format, array $configurations): bool;
}
