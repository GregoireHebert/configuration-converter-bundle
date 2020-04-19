<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Converter;

use ConfigurationConverter\Routing\Resource\ResourceImport;
use ConfigurationConverter\Routing\Resource\ResourceImports;
use Symfony\Component\Routing\RouteCompiler;

class FluentDSLConverter implements RoutingConverterInterface
{
    public const FORMAT = 'dsl';

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

        foreach ($imports as $resource) {
            $space = $baseSpaces;
            $content .= "\n$space\$routes\n";
            $space .= '    ';

            if ($resource->isImport()) {
                $content .= $this->addImport($resource, $space);
            } else {
                $content .= $this->addRoute($resource, $space);
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

    private function addRoute(ResourceImport $resource, string $space)
    {
        $name = $resource->getName();

        $content = sprintf("%s->add('%s', '%s')", $space, $name, $resource->getPath());

        $content .= $this->addCommonFields($resource, $space);

        return $content;
    }

    private function addImport(ResourceImport $resource, string $space): string
    {
        $name = $resource->getName();

        if ($exclude= $resource->getExclude()) {
            $content = sprintf("%s->import('%s', '%s', false, %s)",
                $space,
                $name,
                $resource->getResource(),
                $this->exportVar($resource->getExclude() ?: [], $space)
            );
        } else {
            $content = sprintf("%s->import('%s', '%s')",
                $space,
                $name,
                $resource->getResource()
            );
        }

        if ($prefix = $resource->getPrefix()) {
            $content .= sprintf("\n%s->prefix('%s')", $space, $prefix);
        }

        $content .= $this->addCommonFields($resource, $space);

        return $content;
    }

    private function addCommonFields(ResourceImport $resource, string $space): string
    {
        $content = '';

        if ($defaults = $resource->getDefaults()) {
            if ($defaults['_controller'] ?? false) {
                $content .= sprintf("\n%s->controller('%s')", $space, $defaults['_controller']);
                unset($defaults['_controller']);
            }
            if ($defaults) {
                $content .= sprintf("\n%s->defaults(%s)", $space, $this->exportVar($defaults, $space));
            }
        }

        if ($schemes = $resource->getSchemes()) {
            $content .= sprintf("\n%s->schemes(%s)", $space, $this->exportVar($schemes, $space));
        }

        if ($requirements = $resource->getRequirements()) {
            $content .= sprintf("\n%s->requirements(%s)", $space, $this->exportVar($requirements, $space));
        }

        if ($methods = $resource->getMethods()) {
            $content .= sprintf("\n%s->methods(%s)", $space, $this->exportVar($methods, $space));
        }

        if ($host = $resource->getHost()) {
            $content .= sprintf("\n%s->host('%s')", $space, $host);
        }

        if ($condition = $resource->getCondition()) {
            $content .= sprintf("\n%s->condition('%s')", $space, $condition);
        }

        if ($options = $resource->getOptions()) {
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

        return $content;
    }
}
