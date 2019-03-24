<?php

declare(strict_types=1);

namespace ConfigurationConverter\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('configuration_converter');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('configuration_converter');
        }

        $rootNode
            ->children()
                ->scalarNode('api_platform_default_export_dir')
                    ->info('directory path where to export the API Platform configuration')
                    ->defaultValue('%kernel.project_dir%/config/packages/api-platform/')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
