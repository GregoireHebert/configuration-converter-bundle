<?php

declare(strict_types=1);

namespace ConfigurationConverter\Writers;

use Symfony\Component\Filesystem\Filesystem;

class SerializerGroupFileWriter extends SerializerGroupWriter
{
    public function write(string $filename, string $format, string $exportPath): ?string
    {
        if (empty($exportPath)) {
            return null;
        }

        $fs = new Filesystem();
        foreach ($this->groups as $serializerGroupConfigurationContent) {
            $fs->dumpFile(sprintf('%s/%s.%s', $exportPath, $filename, $format), $serializerGroupConfigurationContent->getData());
        }

        return <<<TXT
Check your configuration in the $exportPath directory, and don't forget to configure Symfony to use it.
```
# config/packages/framework.yaml
framework:
    serializer:
        mapping:
            paths: ['%kernel.project_dir%/config/packages/serialization']
```
TXT;
    }
}
