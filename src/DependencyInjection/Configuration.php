<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('api_platform_configuration_converter');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('default_export_dir')
                    ->info('directory path where to export the configuration')
                    ->defaultValue('%kernel.project_dir%/config/packages/api-platform/')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
