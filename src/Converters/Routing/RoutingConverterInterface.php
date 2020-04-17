<?php

declare(strict_types=1);

namespace ConfigurationConverter\Converters\Routing;

use Symfony\Component\Routing\RouteCollection;

interface RoutingConverterInterface
{
    public function supports(string $outputFormat): bool;

    public function convert(RouteCollection $collection): string;
}
