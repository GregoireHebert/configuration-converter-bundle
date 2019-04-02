<?php

declare(strict_types=1);

namespace ConfigurationConverter\Encoders;

use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use ApiPlatform\Core\Metadata\Property\SubresourceMetadata;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;

class ApiResourceXmlEncoder implements ConfigurationEncoderInterface
{
    private $resourceMetadataFactory;
    private $propertyMetadataFactory;
    private $propertyNameCollectionFactory;

    private $resource;

    public function __construct(
        ResourceMetadataFactoryInterface $annotationResourceMetadataFactory,
        PropertyMetadataFactoryInterface $annotationPropertyMetadataFactory,
        PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory
    ) {
        $this->resourceMetadataFactory = $annotationResourceMetadataFactory;
        $this->propertyMetadataFactory = $annotationPropertyMetadataFactory;
        $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
    }

    public function encode(string $resourceClass): array
    {
        $this->resource = [];
        $this->transformResource($resourceClass);
        $this->transformProperties($resourceClass);

        return $this->resource;
    }

    private function transformResource(string $resourceClass): void
    {
        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

        $this->resource['@class'] = $resourceClass;

        if (null !== $shortName = $resourceMetadata->getShortName()) {
            $this->resource['@shortName'] = $shortName;
        }

        if (null !== $description = $resourceMetadata->getDescription()) {
            $this->resource['@description'] = $description;
        }

        if (null !== $iri = $resourceMetadata->getIri()) {
            $this->resource['@iri'] = $iri;
        }

        $this->resource['attribute'] = $this->getNode('attribute', $resourceMetadata->getAttributes())['attribute'];
        $this->resource['graphql'] = $this->getNode('operation', $resourceMetadata->getGraphql());
        $this->resource['itemOperations'] = $this->getNode('itemOperation', $resourceMetadata->getItemOperations());
        $this->resource['collectionOperations'] = $this->getNode('collectionOperation', $resourceMetadata->getCollectionOperations());
    }

    private function transformProperties(string $resourceClass): void
    {
        $properties = [];

        foreach ($this->propertyNameCollectionFactory->create($resourceClass) as $propertyName) {
            $propertyMetadata = $this->propertyMetadataFactory->create($resourceClass, $propertyName);

            $properties[] = $this->getProperty($propertyMetadata, $propertyName);
        }

        if (!empty($properties)) {
            $this->resource['property'] = $properties;
        }
    }

    private function getProperty(PropertyMetadata $propertyMetadata, string $propertyName): array
    {
        $property = [];
        $property['@name'] = $propertyName;

        if (null !== $description = $propertyMetadata->getDescription()) {
            $property['@description'] = $description;
        }

        if (null !== $iri = $propertyMetadata->getIri()) {
            $property['@iri'] = $iri;
        }

        if (null !== $readable = $propertyMetadata->isReadable()) {
            $property['@readable'] = $readable ? 'true' : 'false';
        }

        if (null !== $writable = $propertyMetadata->isWritable()) {
            $property['@writable'] = $writable ? 'true' : 'false';
        }

        if (null !== $readableLink = $propertyMetadata->isReadableLink()) {
            $property['@readableLink'] = $readableLink ? 'true' : 'false';
        }

        if (null !== $writableLink = $propertyMetadata->isWritableLink()) {
            $property['@writableLink'] = $writableLink ? 'true' : 'false';
        }

        if (null !== $required = $propertyMetadata->isRequired()) {
            $property['@required'] = $required ? 'true' : 'false';
        }

        if (null !== $identifier = $propertyMetadata->isIdentifier()) {
            $property['@identifier'] = $identifier ? 'true' : 'false';
        }

        if (null !== $attribute = $this->getNode('attribute', $propertyMetadata->getAttributes())) {
            $property['attribute'] = $attribute;
        }

        if (null !== $subResource = $this->transformSubResource($propertyMetadata->getSubresource())) {
            $property['subresource'] = $subResource;
        }

        return $property;
    }

    private function transformSubResource(?SubresourceMetadata $subResourceMetadata): ?array
    {
        if (null === $subResourceMetadata) {
            return null;
        }

        $subResource = [];
        $subResource['@resourceClass'] = $subResourceMetadata->getResourceClass();

        if (null !== $getMaxDepth = $subResourceMetadata->getMaxDepth()) {
            $subResource['@maxDepth'] = $getMaxDepth;
        }

        return $subResource;
    }

    private function getNode(string $node, ?array $data): ?array
    {
        if (empty($data)) {
            return null;
        }

        $operations[$node] = [];

        foreach ($data as $key => $value) {
            $entry = ['@name' => \is_string($key) ? $key : $value];

            if (is_iterable($value)) {
                $entry['attribute'] = $this->getAttributes($value);
            } elseif ($entry['@name'] !== $value) {
                $entry['#'] = \is_bool($value) ? ($value ? 'true' : 'false') : $value;
            }

            $operations[$node][] = $entry;
        }

        return $operations;
    }

    private function getAttributes(iterable $configurations): array
    {
        $nodes = [];

        foreach ($configurations as $attribute => $value) {
            if (is_iterable($value)) {
                $value = ['attribute' => $this->getAttributes($value)];
            }

            if (is_numeric($attribute)) {
                $nodes[] = $value;
                continue;
            }

            if ('attributes' === $attribute) {
                continue;
            }

            $nodes[] = ['@name' => $attribute, '#' => $value];
        }

        return $nodes;
    }

    private function getArguments(iterable $arguments): array
    {
        $nodes = [];

        foreach ($arguments as $attribute => $value) {
            if (is_iterable($value)) {
                $value = ['argument' => $this->getArguments($value)];
            }

            if (is_numeric($attribute)) {
                $nodes[] = $value;
            } else {
                $nodes[] = ['@key' => $attribute, '#' => $value];
            }
        }

        return $nodes;
    }
}
