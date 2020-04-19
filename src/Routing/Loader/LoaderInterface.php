<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Loader;

use ConfigurationConverter\Routing\Resource\ResourceImports;

interface LoaderInterface
{
    public function supports($resource): bool;

    public function load($resource): ResourceImports;
}
