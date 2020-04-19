<?php


namespace ConfigurationConverter\Routing\Loader;

class YamlFileLoader implements LoaderInterface
{
    public function supports($resource): bool
    {
        if (!\is_string($resource)) {
            return false;
        }

        return \in_array(\strtolower(\pathinfo($resource, PATHINFO_EXTENSION)), ['yaml', 'yml']);
    }

    public function load($resource): ResourceImports
    {
        dd($resource);
    }
}
