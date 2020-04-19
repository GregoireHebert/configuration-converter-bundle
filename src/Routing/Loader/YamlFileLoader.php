<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Loader;

use ConfigurationConverter\Routing\Converter\Loader\ResourceImports;

class YamlFileLoader implements LoaderInterface
{
    public function supports($resource): bool
    {
        if (!\is_string($resource)) {
            return false;
        }

        return \in_array(strtolower(pathinfo($resource, PATHINFO_EXTENSION)), ['yaml', 'yml'], true);
    }

    public function load($resource): ResourceImports
    {
        dd($resource);
    }
}
