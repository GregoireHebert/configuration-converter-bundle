<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Converter;

use ConfigurationConverter\Routing\Resource\ResourceImports;

interface RoutingConverterInterface
{
    public function supports(string $outputFormat): bool;

    public function convert(ResourceImports $imports): string;
}
