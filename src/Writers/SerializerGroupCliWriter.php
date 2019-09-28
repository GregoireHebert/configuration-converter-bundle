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
Check and paste this configuration:
# config/packages/serialization/$shortName.$format

{$serializerGroupConfigurationContent->getData()}
TXT;
        }

        return $result;
    }
}
