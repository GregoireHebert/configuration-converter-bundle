<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Test\DependencyInjection;

use ApiPlatform\ConfigurationConverter\DependencyInjection\ConfigurationConverterExtension;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class ConfigurationConverterExtensionTest extends TestCase
{
    const DEFAULT_CONFIG = ['api_platform_configuration_converter' => [
        'default_export_dir' => '%kernel.project_dir%/config/packages/api-platform/',
    ]];

    private $extension;
    private $childDefinitionProphecy;

    protected function setUp(): void
    {
        $this->extension = new ConfigurationConverterExtension();
        $this->childDefinitionProphecy = $this->prophesize(ChildDefinition::class);
    }

    public function tearDown(): void
    {
        unset($this->extension);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
        $this->assertInstanceOf(ConfigurationExtensionInterface::class, $this->extension);
    }

    public function testLoadDefaultConfig(): void
    {
        $containerBuilderProphecy = $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $containerBuilderProphecy->fileExists(Argument::type('string'))->shouldBeCalled();
        $containerBuilderProphecy->setParameter('api_platform.configuration_converter.default_export_dir', '%kernel.project_dir%/config/packages/api-platform/')->shouldBeCalled();
        $containerBuilderProphecy->hasExtension('http://symfony.com/schema/dic/services')->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('api_platform.configuration_converter.command.api_resource_convert_configuration_command', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('api_platform.configuration_converter.data_transformers.xml_transformer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilder = $containerBuilderProphecy->reveal();

        $this->extension->load(self::DEFAULT_CONFIG, $containerBuilder);
    }
}
