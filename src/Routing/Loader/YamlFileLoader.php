<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Loader;

use ConfigurationConverter\Routing\Resource\ResourceImport;
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

        foreach ($parsedYaml as $name => $config) {
            $condition = $config['condition'] ?? null;
            $defaults = $config['defaults'] ?? [];
            $host = $config['host'] ?? '';
            $methods = $config['methods'] ?? [];
            $options = $config['options'] ?? [];
            $prefix = $config['prefix'] ?? '';
            $requirements = $config['requirements'] ?? [];
            $schemes = $config['schemes'] ?? [];
            $trailingSlashOnRoot = $config['trailing_slash_on_root'] ?? true;
            $type = $config['type'] ?? null;
            $exclude = $config['exclude'] ?? null;

            if (isset($config['controller'])) {
                $defaults['_controller'] = $config['controller'];
            }
            if (isset($config['locale'])) {
                $defaults['_locale'] = $config['locale'];
            }
            if (isset($config['format'])) {
                $defaults['_format'] = $config['format'];
            }
            if (isset($config['utf8'])) {
                $options['utf8'] = $config['utf8'];
            }
            if (!is_array($exclude)) {
                $exclude = [$exclude];
            }

            if (isset($config['resource'])) {
                $imports->addResource(
                    ResourceImport::fromImport(
                        $name,
                        $config['resource'],
                        $type,
                        $prefix,
                        $defaults,
                        $requirements,
                        $options,
                        $host,
                        $condition,
                        $schemes,
                        $methods,
                        $trailingSlashOnRoot,
                        $exclude
                    )
                );
            } else {
                $imports->addResource(
                    ResourceImport::fromRoute(
                        $name,
                        $config['path'],
                        $defaults,
                        $requirements,
                        $options,
                        $host,
                        $schemes,
                        $methods,
                        $condition
                    )
                );

            }
        }

        return $imports;
    }
}
