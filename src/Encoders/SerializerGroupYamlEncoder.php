<?php

declare(strict_types=1);

namespace ConfigurationConverter\Encoders;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

class SerializerGroupYamlEncoder implements ConfigurationEncoderInterface
{
    private $resource;
    private $classMetadataFactory;

    public function __construct()
    {
        $this->classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
    }

    public function encode(string $resourceClass): array
    {
        $this->resource = [];
        $this->transformGroups($resourceClass);

        if (empty($this->resource)) {
            return [];
        }

        return [$resourceClass => ['attributes' => $this->resource]];
    }

    private function transformGroups(string $resourceClass): void
    {
        $resourceMetadata = $this->classMetadataFactory->getMetadataFor($resourceClass);

        foreach ($resourceMetadata->getAttributesMetadata() as $attribute) {
            if (empty($groups = $attribute->getGroups())) {
                continue;
            }

            $this->resource[$attribute->getName()]['groups'] = $groups;
        }
    }
}
