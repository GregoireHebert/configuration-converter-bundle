<?php

declare(strict_types=1);

namespace ConfigurationConverter\Writers;

class ApiPlatformCliWriter extends ApiPlatformWriter
{
    public function write(string $shortName, string $format, string $exportPath): ?string
    {
        if (!empty($exportPath)) {
            return null;
        }

        $result = null;

        foreach ($this->resources as $apiResourceConfigurationContent) {
            $result .= <<<TXT
Check and paste this configuration:
# config/packages/api-platform/$shortName.$format

{$apiResourceConfigurationContent->getData()}
TXT;
        }

        foreach ($this->filters as $apiFilterConfigurationContent) {
            $result .= <<<TXT
# config/packages/api-platform/$shortName.services.$format

{$apiFilterConfigurationContent->getData()}
TXT;
        }

        return $result;
    }
}
