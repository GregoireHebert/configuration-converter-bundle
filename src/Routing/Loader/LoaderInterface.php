<?php

namespace ConfigurationConverter\Routing\Loader;

interface LoaderInterface
{
    public function supports($resource): bool;

    public function load($resource): ResourceImports;
}
