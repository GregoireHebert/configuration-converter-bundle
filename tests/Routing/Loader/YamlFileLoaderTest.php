<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Routing\Loader;

use ConfigurationConverter\Routing\Loader\YamlFileLoader;
use PHPUnit\Framework\TestCase;

class YamlFileLoaderTest extends TestCase
{
    public function testFixtureFileImport(): void
    {
        $loader = new YamlFileLoader();

        $routeFile = __DIR__.'/../../Fixtures/App/config/routes/empty_routing_file_to_convert.yaml';

        static::assertTrue($loader->supports($routeFile));

        $resources = $loader->load($routeFile);
    }
}
