<?php

declare(strict_types=1);

namespace ConfigurationConverter\Encoders;

use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use ApiPlatform\Core\Metadata\Property\SubresourceMetadata;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;

class ApiResourceYmlEncoder implements ConfigurationEncoderInterface
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

        return [$resourceClass => $this->resource];
    }

    private function transformResource(string $resourceClass): void
    {
        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

        if (null !== $shortName = $resourceMetadata->getShortName()) {
            $this->resource['shortName'] = $shortName;
        }

        if (null !== $description = $resourceMetadata->getDescription()) {
            $this->resource['description'] = $description;
        }

        if (null !== $iri = $resourceMetadata->getIri()) {
            $this->resource['iri'] = $iri;
        }

        $this->resource['attributes'] = $this->cleanNode($resourceMetadata->getAttributes());
        $this->resource['graphql'] = $this->cleanNode($resourceMetadata->getGraphql());
        $this->resource['itemOperations'] = $this->cleanNode($resourceMetadata->getItemOperations());
        $this->resource['collectionOperations'] = $this->cleanNode($resourceMetadata->getCollectionOperations());
    }

    private function transformProperties(string $resourceClass): void
    {
        $properties = [];

        foreach ($this->propertyNameCollectionFactory->create($resourceClass) as $propertyName) {
            $propertyMetadata = $this->propertyMetadataFactory->create($resourceClass, $propertyName);

            $properties[$propertyName] = $this->getProperty($propertyMetadata);
        }

        if (!empty($properties)) {
            $this->resource['properties'] = $properties;
        }
    }

    private function getProperty(PropertyMetadata $propertyMetadata): array
    {
        $property = [];
        $property['description'] = $propertyMetadata->getDescription();
        $property['iri'] = $propertyMetadata->getIri();
        $property['readable'] = $propertyMetadata->isReadable();
        $property['writable'] = $propertyMetadata->isWritable();
        $property['readableLink'] = $propertyMetadata->isReadableLink();
        $property['writableLink'] = $propertyMetadata->isWritableLink();
        $property['required'] = $propertyMetadata->isRequired();
        $property['identifier'] = $propertyMetadata->isIdentifier();
        $property['attributes'] = $this->cleanNode($propertyMetadata->getAttributes());

        $property['subresource'] = $this->transformSubResource($propertyMetadata->getSubresource());

        return array_filter($property);
    }

    private function transformSubResource(?SubresourceMetadata $subResourceMetadata): ?array
    {
        if (null === $subResourceMetadata) {
            return null;
        }

        $subResource = [];
        $subResource['resourceClass'] = $subResourceMetadata->getResourceClass();

        if (null !== $getMaxDepth = $subResourceMetadata->getMaxDepth()) {
            $subResource['maxDepth'] = $getMaxDepth;
        }

        return $subResource;
    }

    private function cleanNode(?array $data, $depth = 0): ?array
    {
        if (empty($data)) {
            return null;
        }

        $node = [];

        foreach ($data as $key => $value) {
            if (null === $value || empty($value)) {
                continue;
            }

            if (is_iterable($value)) {
                $node[$key] = $this->cleanNode((array) $value, $depth + 1);
                continue;
            }

            if (is_numeric($key) && 0 === $depth) {
                $node[$value] = null;
                continue;
            }

            if (is_numeric($key)) {
                $node[] = $value;
                continue;
            }

            $node[$key] = $value;
        }

        return $node;
    }
}
