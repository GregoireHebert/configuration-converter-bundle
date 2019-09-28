<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\DependencyInjection;

use ConfigurationConverter\DependencyInjection\Configuration;
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
        $config = $this->processor->processConfiguration($this->configuration, [
            'configuration_converter' => [
                'api_platform_default_export_dir' => 'my/dir',
                'serializer_group' => [
                    'default_export_dir' => 'my/export/group/dir',
                    'entities_dir' => ['my/entities/dir'],
                ],
            ],
        ]);

        $this->assertInstanceOf(ConfigurationInterface::class, $this->configuration);
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
        $this->assertEquals([
            'api_platform_default_export_dir' => 'my/dir',
            'serializer_group' => [
                'default_export_dir' => 'my/export/group/dir',
                'entities_dir' => ['my/entities/dir'],
            ],
        ], $config);
    }

    public function testEmptyDefaultExportDirDescriptionConfig(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            'configuration_converter' => [],
        ]);

        $this->assertSame($config['api_platform_default_export_dir'], '%kernel.project_dir%/config/packages/api-platform/');
        $this->assertSame($config['serializer_group'], [
            'default_export_dir' => '%kernel.project_dir%/config/packages/serialization/',
            'entities_dir' => ['%kernel.project_dir%/src/Entity/'],
        ]);
    }
}
