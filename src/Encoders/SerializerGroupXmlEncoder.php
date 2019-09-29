<?php

declare(strict_types=1);

namespace ConfigurationConverter\Encoders;

final class SerializerGroupXmlEncoder extends SerializerGroupYamlEncoder
{
    public function encode(string $resourceClass): array
    {
        $this->resource = [];
        $this->transformGroups($resourceClass);

        if (empty($this->resource)) {
            return [];
        }

        return [
            '@name' => $resourceClass,
            'attribute' => $this->resource,
        ];
    }

    protected function transformGroups(string $resourceClass): void
    {
        $resourceMetadata = $this->classMetadataFactory->getMetadataFor($resourceClass);

        foreach ($resourceMetadata->getAttributesMetadata() as $attribute) {
            if (empty($groups = $attribute->getGroups())) {
                continue;
            }

            $this->resource[] = [
                '@name' => $attribute->getName(),
                'group' => $groups,
            ];
        }
    }
}
