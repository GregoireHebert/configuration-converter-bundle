<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Converter;

use ConfigurationConverter\Routing\Resource\ResourceImports;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Routing\RouteCompiler;

class FluentDSLConverter implements RoutingConverterInterface
{
    public const FORMAT = 'dsl';

    private FileLocatorInterface $fileLocator;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    public function supports(string $outputFormat): bool
    {
        return self::FORMAT === $outputFormat;
    }

    public function convert(ResourceImports $imports): string
    {
        $content = '<?php
use Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator;

return function (RoutingConfigurator $routes) {';

        $baseSpaces = '    ';

        return 'KO';
        foreach ($routes->all() as $name => $route) {
            $space = $baseSpaces;
            $content .= "\n$space\$routes\n";
            $space .= '    ';
            $content .= sprintf("%s->add('%s', '%s')", $space, $name, $route->getPath());

            if ($defaults = $route->getDefaults()) {
                if ($defaults['_controller'] ?? false) {
                    $content .= sprintf("\n%s->controller('%s')", $space, $defaults['_controller']);
                    unset($defaults['_controller']);
                }
                if ($defaults) {
                    $content .= sprintf("\n%s->defaults(%s)", $space, $this->exportVar($defaults, $space));
                }
            }

            if ($schemes = $route->getSchemes()) {
                $content .= sprintf("\n%s->schemes(%s)", $space, $this->exportVar($schemes, $space));
            }

            if ($requirements = $route->getRequirements()) {
                $content .= sprintf("\n%s->requirements(%s)", $space, $this->exportVar($requirements, $space));
            }

            if ($methods = $route->getMethods()) {
                $content .= sprintf("\n%s->methods(%s)", $space, $this->exportVar($methods, $space));
            }

            if ($host = $route->getHost()) {
                $content .= sprintf("\n%s->host('%s')", $space, $host);
            }

            if ($condition = $route->getCondition()) {
                $content .= sprintf("\n%s->condition('%s')", $space, $condition);
            }

            if ($options = $route->getOptions()) {
                if ($options['utf8'] ?? false) {
                    $content .= sprintf("\n%s->utf8()", $space);
                    unset($options['utf8']);
                }
                if (isset($options['compiler_class']) && RouteCompiler::class === $options['compiler_class']) {
                    unset($options['compiler_class']); // This is the default already.
                }
                if ($options) {
                    $content .= sprintf("\n%s->options(%s)", $space, $this->exportVar($options, $space));
                }
            }

            $content .= "\n    ;";
        }

        $content .= "\n};\n";

        return $content;
    }

    private function exportVar($varToExport, string $space): string
    {
        $export = var_export($varToExport, true);

        return str_replace(["\r", "\n"], ['', "\n$space"], $export);
    }
}
