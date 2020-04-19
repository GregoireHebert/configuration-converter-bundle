<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Routing\Converters;

use ConfigurationConverter\Routing\Converter\FluentDSLConverter;
use ConfigurationConverter\Routing\Loader\YamlFileLoader;
use PHPUnit\Framework\TestCase;

class FluentDSLConverterTest extends TestCase
{
    public function testFluent(): void
    {
        $routeFile = __DIR__.'/../../Fixtures/App/config/routes/default_routing_file_to_convert.yaml';
        $resources = (new YamlFileLoader())->load($routeFile);
        $converter = new FluentDSLConverter();

        $converted = $converter->convert($resources);

        static::assertSame(<<<'CONVERTED'
            <?php
            use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
            
            return function (RoutingConfigurator $routes) {
                $routes
                    ->add('route_one', '/one')
                    ->controller('Any controller')
                    ->defaults(array (
                      'something' => 'value',
                    ))
                    ->requirements(array (
                      'something' => '\\w+',
                    ))
                    ->condition('request.isMethod('POST')')
                ;
                $routes
                    ->import('imported_resource', 'imported_file.yaml')
                ;
            };
            
            CONVERTED,
            $converted
        );
    }
}
