<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Test\DependencyInjection;

use ApiPlatform\ConfigurationConverter\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Processor
     */
    private $processor;

    public function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testDefaultConfig(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();
        $config = $this->processor->processConfiguration($this->configuration, ['api_platform_configuration_converter' => ['default_export_dir' => 'my/dir']]);

        $this->assertInstanceOf(ConfigurationInterface::class, $this->configuration);
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
        $this->assertEquals([
            'default_export_dir' => 'my/dir',
        ], $config);
    }

    public function testEmptyDefaultExportDirDescriptionConfig(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            'api_platform_configuration_converter' => [],
        ]);

        $this->assertSame($config['default_export_dir'], '%kernel.project_dir%/config/packages/api-platform/');
    }
}
