<?php

declare(strict_types=1);

namespace ConfigurationConverter\Writers;

class SerializerGroupCliWriter extends SerializerGroupWriter
{
    public function write(string $shortName, string $format, string $exportPath): ?string
    {
        if (!empty($exportPath)) {
            return null;
        }

        $result = null;

        foreach ($this->groups as $serializerGroupConfigurationContent) {
            $result .= <<<TXT
Configure Symfony to use your yaml groups

```
# config/packages/framework.yaml
framework:
    serializer:
        mapping:
            paths: ['%kernel.project_dir%/config/packages/serialization']
```

Check and paste this configuration:
# config/packages/serialization/$shortName.$format

{$serializerGroupConfigurationContent->getData()}
TXT;
        }

        return $result;
    }
}
