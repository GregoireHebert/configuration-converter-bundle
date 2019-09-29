<?php

declare(strict_types=1);

namespace ConfigurationConverter\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class ConfigurationConverterExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('configuration_converter.api_platform_default_export_dir', $config['api_platform_default_export_dir']);
        $container->setParameter('configuration_converter.serializer_group.default_export_dir', $config['serializer_groups']['default_export_dir']);
        $container->setParameter('configuration_converter.serializer_group.entities_dir', $config['serializer_groups']['entities_dir']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('command.xml');
        $loader->load('encoders.xml');
        $loader->load('converters.xml');
        $loader->load('serializers.xml');
        $loader->load('writers.xml');
    }
}
