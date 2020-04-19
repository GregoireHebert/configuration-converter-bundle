<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Loader;

use ConfigurationConverter\Routing\Resource\ResourceImports;
use Symfony\Component\Yaml\Yaml;

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
        $imports = new ResourceImports();

        $parsedYaml = Yaml::parseFile($resource);

        if (null !== $parsedYaml && !is_array($parsedYaml)) {
            throw new \InvalidArgumentException(\sprintf(
                'Yaml file %s was expected to be parsed as an array, %s given.',
                $resource, get_debug_type($parsedYaml)
            ));
        }

        if (!$parsedYaml) {
            return $imports;
        }

        return $imports;
    }
}
