<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\DependencyInjection;

use ConfigurationConverter\Command\ConverterCommand;
use ConfigurationConverter\DependencyInjection\ConfigurationConverterExtension;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class ConfigurationConverterExtensionTest extends KernelTestCase
{
    const DEFAULT_CONFIG = ['api_platform_configuration_converter' => [
        'api_platform_default_export_dir' => '%kernel.project_dir%/config/packages/api-platform/',
        'serializer_groups' => [
            'default_export_dir' => '%kernel.project_dir%/config/packages/serializer/',
            'entities_dir' => ['%kernel.project_dir%/src/Entity/'],
        ],
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
        $containerBuilderProphecy->setParameter('configuration_converter.serializer_group.default_export_dir', '%kernel.project_dir%/config/packages/serializer/')->shouldBeCalled();
        $containerBuilderProphecy->setParameter('configuration_converter.serializer_group.entities_dir', ['%kernel.project_dir%/src/Entity/'])->shouldBeCalled();
        $containerBuilderProphecy->hasExtension('http://symfony.com/schema/dic/services')->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.command.api_resource_convert_configuration_command', Argument::type(Definition::class))->shouldBeCalled();

        $containerBuilderProphecy->setDefinition('configuration_converter.encoders.api_platform.api_filter_xml_encoder', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.encoders.api_platform.api_filter_yaml_encoder', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.encoders.api_platform.api_resource_xml_encoder', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.encoders.api_platform.api_resource_yaml_encoder', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.encoders.serializer_group.yaml_encoder', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.encoders.serializer_group.xml_encoder', Argument::type(Definition::class))->shouldBeCalled();

        $containerBuilderProphecy->setDefinition('configuration_converter.converters.configuration_converter', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.converters.api_platform.xml_converter', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.converters.api_platform.yaml_converter', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.converters.serializer_group.yaml_converter', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.converters.serializer_group.xml_converter', Argument::type(Definition::class))->shouldBeCalled();

        $containerBuilderProphecy->setDefinition('configuration_converter.serializers.api_platform.api_resource_xml_serializer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.serializers.api_platform.api_resource_yaml_serializer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.serializers.api_platform.api_filter_xml_serializer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.serializers.api_platform.api_filter_yaml_serializer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.serializers.serializer_group.yaml_serializer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.serializers.serializer_group.xml_serializer', Argument::type(Definition::class))->shouldBeCalled();

        $containerBuilderProphecy->setDefinition('configuration_converter.writers.api_platform.writer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.writers.api_platform.cli_writer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.writers.api_platform.file_writer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.writers.serializer_group.writer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.writers.serializer_group.cli_writer', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->setDefinition('configuration_converter.writers.serializer_group.file_writer', Argument::type(Definition::class))->shouldBeCalled();

        // irrelevant, but to prevent errors
        if (method_exists(ContainerBuilder::class, 'removeBindings')) {
            $containerBuilderProphecy->removeBindings(Argument::type('string'))->will(function () {});
        }

        $containerBuilder = $containerBuilderProphecy->reveal();

        $this->extension->load(self::DEFAULT_CONFIG, $containerBuilder);
    }
}
