<?php

declare(strict_types=1);

namespace ConfigurationConverter\Writers;

use Symfony\Component\Filesystem\Filesystem;

class ApiPlatformFileWriter extends ApiPlatformWriter
{
    public function write(string $filename, string $format, string $exportPath): ?string
    {
        if (empty($exportPath)) {
            return null;
        }

        $fs = new Filesystem();
        foreach ($this->resources as $apiResourceConfigurationContent) {
            $fs->dumpFile(sprintf('%s/%s.%s', $exportPath, $filename, $format), $apiResourceConfigurationContent->getData());
        }

        foreach ($this->filters as $apiFilterConfigurationContent) {
            $fs->dumpFile(sprintf('%s/%s.services.%s', $exportPath, $filename, $format), $apiFilterConfigurationContent->getData());
        }

        return <<<TXT
Check your configuration in the $exportPath directory, and don't forget to configure API Platform to use it.
https://api-platform.com/docs/core/getting-started/#mapping-the-entities
TXT;
    }
}
