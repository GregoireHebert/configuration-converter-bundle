<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\DependencyInjection;

use ConfigurationConverter\DependencyInjection\ConfigurationConverterExtension;
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
        'api_platform_default_export_dir' => '%kernel.project_dir%/config/packages/api-platform/',
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
        $containerBuilderProphecy->setParameter('configuration_converter.api_platform_default_export_dir', '%kernel.project_dir%/config/packages/api-platform/')->shouldBeCalled();
        $containerBuilderProphecy->hasExtension('http://symfony.com/schema/dic/services')->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.command.api_resource_convert_configuration_command', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.encoders.api_platform.api_filter_xml_encoder', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.encoders.api_platform.api_resource_xml_encoder', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.converters.configuration_converter', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.converters.api_platform_xml_converter', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.serializers.api_platform.api_resource_xml_serializer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.serializers.api_platform.api_filter_xml_serializer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.writers.api_platform_writer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.writers.api_platform_cli_writer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.writers.api_platform_file_writer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilder = $containerBuilderProphecy->reveal();

        $this->extension->load(self::DEFAULT_CONFIG, $containerBuilder);
    }
}
