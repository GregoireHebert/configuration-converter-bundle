<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Routing\Loader;

use ConfigurationConverter\Routing\Loader\YamlFileLoader;
use PHPUnit\Framework\TestCase;

class YamlFileLoaderTest extends TestCase
{
    /**
     * @dataProvider provideYamlExtensionFilenames
     */
    public function testSupportOnlyYamlExtensionFiles(string $fileName): void
    {
        $loader = new YamlFileLoader();

        static::assertTrue($loader->supports($fileName));
    }

    public function provideYamlExtensionFilenames(): \Generator
    {
        yield ['some_file.yaml'];
        yield ['some_file.yml'];
        yield ['some_file.YAML'];
        yield ['some_file.YmL'];
    }

    public function testInvalidYamlFileThrowsException(): void
    {
        $loader = new YamlFileLoader();

        $routeFile = __DIR__.'/../../Fixtures/App/config/routes/invalid_file.yaml';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Yaml file %s was expected to be parsed as an array, %s given.',
            $routeFile, 'string'
        ));

        $loader->load($routeFile);
    }

    public function testEmptyFileReturnsEmptyResources(): void
    {
        $loader = new YamlFileLoader();

        $routeFile = __DIR__.'/../../Fixtures/App/config/routes/empty_file.yaml';

        $resources = $loader->load($routeFile);

        static::assertCount(0, $resources);
    }

    public function testFixtureFileImport(): void
    {
        $loader = new YamlFileLoader();

        $routeFile = __DIR__.'/../../Fixtures/App/config/routes/default_routing_file_to_convert.yaml';

        static::assertTrue($loader->supports($routeFile));

        $resources = $loader->load($routeFile);

        static::assertCount(2, $resources);

        $iterator = $resources->getIterator();

        static::assertTrue($iterator[0]->isRoute());
        static::assertSame('route_one', $iterator[0]->getName());
        static::assertTrue($iterator[1]->isImport());
        static::assertSame('imported_resource', $iterator[1]->getName());
    }
}
