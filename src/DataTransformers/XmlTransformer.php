<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\DataTransformers;

use ApiPlatform\Core\Api\FilterLocatorTrait;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\AbstractFilter as MongoDbOdmAbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\BooleanFilter as MongoDbOdmBooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\DateFilter as MongoDbOdmDateFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\ExistsFilter as MongoDbOdmExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\NumericFilter as MongoDbOdmNumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\OrderFilter as MongoDbOdmOrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\RangeFilter as MongoDbOdmRangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\SearchFilter as MongoDbOdmSearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use ApiPlatform\Core\Metadata\Property\SubresourceMetadata;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class XmlTransformer implements ConfigurationConverterInterface
{
    use FilterLocatorTrait;

    private const FILTERS_SERVICES_ID = [
        SearchFilter::class => 'api_platform.doctrine.orm.search_filter',
        OrderFilter::class => 'api_platform.doctrine.orm.order_filter',
        RangeFilter::class => 'api_platform.doctrine.orm.range_filter',
        DateFilter::class => 'api_platform.doctrine.orm.date_filter',
        BooleanFilter::class => 'api_platform.doctrine.orm.boolean_filter',
        NumericFilter::class => 'api_platform.doctrine.orm.numeric_filter',
        ExistsFilter::class => 'api_platform.doctrine.orm.exists_filter',
        MongoDbOdmSearchFilter::class => 'api_platform.doctrine_mongodb.odm.search_filter',
        MongoDbOdmBooleanFilter::class => 'api_platform.doctrine_mongodb.odm.boolean_filter',
        MongoDbOdmDateFilter::class => 'api_platform.doctrine_mongodb.odm.date_filter',
        MongoDbOdmExistsFilter::class => 'api_platform.doctrine_mongodb.odm.exists_filter',
        MongoDbOdmNumericFilter::class => 'api_platform.doctrine_mongodb.odm.numeric_filter',
        MongoDbOdmOrderFilter::class => 'api_platform.doctrine_mongodb.odm.order_filter',
        MongoDbOdmRangeFilter::class => 'api_platform.doctrine_mongodb.odm.range_filter',
        PropertyFilter::class => 'api_platform.serializer.property_filter',
        GroupFilter::class => 'api_platform.serializer.group_filter',
    ];

    private $resourceMetadataFactory;
    private $propertyMetadataFactory;
    private $propertyNameCollectionFactory;
    private $resourceFilterMetadataFactory;
    private $filterServicesDefinition;
    private $encodedFilterServicesDefinition;

    private $resource;

    public function __construct(
        ResourceMetadataFactoryInterface $annotationResourceMetadataFactory,
        PropertyMetadataFactoryInterface $annotationPropertyMetadataFactory,
        ResourceMetadataFactoryInterface $annotationResourceFilterMetadataFactory,
        $filterLocator,
        PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory
    ) {
        $this->resourceMetadataFactory = $annotationResourceMetadataFactory;
        $this->propertyMetadataFactory = $annotationPropertyMetadataFactory;
        $this->resourceFilterMetadataFactory = $annotationResourceFilterMetadataFactory;
        $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
        $this->setFilterLocator($filterLocator);
    }

    public function getName(): string
    {
        return 'xml';
    }

    private function init(): void
    {
        $this->filterServicesDefinition = [];
        $this->resource = [];
        $this->encodedFilterServicesDefinition = null;
    }

    public function transform(string $resourceClass): string
    {
        $this->init();
        $this->transformResource($resourceClass);
        $this->transformProperties($resourceClass);
        $this->transformFilters($resourceClass);

        $serializer = new Serializer([new ObjectNormalizer()], [new XmlEncoder(), new YamlEncoder()]);

        $services = array_values($this->filterServicesDefinition);

        if (!empty($services)) {
            $this->encodedFilterServicesDefinition = $serializer->encode(
                [
                    '@xmlns' => 'http://symfony.com/schema/dic/services',
                    '@xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                    '@xsi:schemaLocation' => 'http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd',
                    'services' => ['service' => $services],
                ],
                'xml',
                [
                    XmlEncoder::ROOT_NODE_NAME => 'container',
                    XmlEncoder::REMOVE_EMPTY_TAGS => true,
                    XmlEncoder::AS_COLLECTION => true,
                    XmlEncoder::FORMAT_OUTPUT => true,
                ]
            );
        }

        return (string) $serializer->encode(
            [
                '@xmlns' => 'https://api-platform.com/schema/metadata',
                '@xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                '@xsi:schemaLocation' => 'https://api-platform.com/schema/metadata https://api-platform.com/schema/metadata/metadata-2.0.xsd',
                'resource' => $this->resource,
            ],
            'xml',
            [
                XmlEncoder::ROOT_NODE_NAME => 'resources',
                XmlEncoder::REMOVE_EMPTY_TAGS => true,
                XmlEncoder::AS_COLLECTION => true,
                XmlEncoder::FORMAT_OUTPUT => true,
            ]
        );
    }

    public function getFiltersServiceDefinition(): ?string
    {
        return $this->encodedFilterServicesDefinition;
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

        if ((null !== $readable = $propertyMetadata->isReadable())) {
            $property['@readable'] = $readable ? 'true' : 'false';
        }

        if ((null !== $writable = $propertyMetadata->isWritable())) {
            $property['@writable'] = $writable ? 'true' : 'false';
        }

        if ((null !== $readableLink = $propertyMetadata->isReadableLink())) {
            $property['@readableLink'] = $readableLink ? 'true' : 'false';
        }

        if ((null !== $writableLink = $propertyMetadata->isWritableLink())) {
            $property['@writableLink'] = $writableLink ? 'true' : 'false';
        }

        if ((null !== $required = $propertyMetadata->isRequired())) {
            $property['@required'] = $required ? 'true' : 'false';
        }

        if ((null !== $identifier = $propertyMetadata->isIdentifier())) {
            $property['@identifier'] = $identifier ? 'true' : 'false';
        }

        if (null !== $attribute = $this->getNode('attribute', $propertyMetadata->getAttributes())) {
            $property['attribute'] = $attribute;
        }

        if (null !== $subresource = $this->transformSubResource($propertyMetadata->getSubresource())) {
            $property['subresource'] = $subresource;
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

    private function transformFilters(string $resourceClass): void
    {
        $resourceMetadata = $this->resourceFilterMetadataFactory->create($resourceClass);
        $shortName = $resourceMetadata->getShortName();
        $collectionOperations = $resourceMetadata->getCollectionOperations() ?? [];

        // Specify the filter when defined with the @ApiFilter Annotation
        $globalResourceFilters = $resourceMetadata->getAttribute('filters');
        $isCustomOperation = null;

        // Specify the filters accordingly to the collection operations definitions.
        foreach ($collectionOperations as $operationName => $operation) {
            if (null === $resourceFilters = $operation['attributes']['filters'] ?? null) {
                continue;
            }

            if (!$isCustomOperation = \is_array($operation)) {
                $operationName = $operation;
            }

            if ('get' !== $operationName && $isCustomOperation && Request::METHOD_GET !== ($operation['method'] ?? null)) {
                continue;
            }

            $this->transformOperationFilter(array_merge($resourceFilters, $globalResourceFilters), $operationName, $shortName);
        }

        // None defined through each collection operation, they've been defined by annotation only
        if (null === $isCustomOperation && null !== $globalResourceFilters) {
            $this->transformOperationFilter($globalResourceFilters, 'get', $shortName);
        }
    }

    private function transformOperationFilter(array $resourceFilters, string $operationName, string $resourceShortName): void
    {
        if (!isset($this->resource['collectionOperations']['collectionOperation'])) {
            $this->resource['collectionOperations']['collectionOperation'][] = [
                '@name' => 'get',
                'attribute' => null,
            ];
        }

        // Update the services
        foreach ($resourceFilters as $key => &$filterId) {
            if (null === $filter = $this->getFilter($filterId)) {
                unset($resourceFilters[$key]);
                continue;
            }

            if (0 !== stripos($filterId, $resourceShortName)) {
                $filterId = sprintf('%s.%s', $resourceShortName, (new \ReflectionClass($filter))->getShortName());
            } else {
                continue;
            }

            $resourceFilters[$key] = $filterId;
            $arguments = [];

            if ($filter instanceof AbstractFilter || $filter instanceof MongoDbOdmAbstractFilter) {
                $closure = \Closure::bind(static function ($filter) { return $filter->properties; }, null, $filter);
                $arguments = $this->getArguments($closure($filter));
            }

            if ($filter instanceof GroupFilter) {
                $closure = \Closure::bind(static function ($filter) {
                    return [
                        ['@key' => '$overrideDefaultGroups', '#' => $filter->overrideDefaultGroups ? 'true' : 'false'],
                        ['@key' => '$parameterName', '#' => $filter->parameterName],
                        ['@key' => '$whitelist', '#' => ['argument' => $filter->whitelist]],
                    ];
                }, null, $filter);
                $arguments = $closure($filter);
            }

            if ($filter instanceof PropertyFilter) {
                $closure = \Closure::bind(static function ($filter) {
                    return [
                        ['@key' => '$overrideDefaultProperties', '#' => $filter->overrideDefaultProperties ? 'true' : 'false'],
                        ['@key' => '$parameterName', '#' => $filter->parameterName],
                        ['@key' => '$whitelist', '#' => ['argument' => $filter->whitelist]],
                    ];
                }, null, $filter);
                $arguments = $closure($filter);
            }

            $this->filterServicesDefinition[$filterId] = [
                '@id' => $filterId,
                '@autowire' => 'false',
                '@autoconfigure' => 'false',
                '@public' => 'false',
                '@parent' => self::FILTERS_SERVICES_ID[\get_class($filter)],
                'argument' => $arguments,
                'tag' => [
                    '@name' => 'api_platform.filter',
                ],
            ];
        }

        // Update the collection operations
        foreach ($this->resource['collectionOperations']['collectionOperation'] as &$operation) {
            if ($operation['@name'] !== $operationName) {
                return;
            }

            $operation['attribute'][] = [
                '@name' => 'filters',
                'attribute' => $resourceFilters,
            ];
        }
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
