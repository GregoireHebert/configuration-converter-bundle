<?php

declare(strict_types=1);

namespace ConfigurationConverter\Encoders;

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
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Symfony\Component\HttpFoundation\Request;

class ApiFilterXmlEncoder implements ConfigurationEncoderInterface
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
    private $resource = [];

    public function __construct(
        ResourceMetadataFactoryInterface $annotationResourceFilterMetadataFactory,
        $filterLocator
    ) {
        $this->resourceFilterMetadataFactory = $annotationResourceFilterMetadataFactory;
        $this->setFilterLocator($filterLocator);
    }

    public function fromEncodedApiResource(array $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function encode(string $resourceClass): array
    {
        $this->filterServicesDefinition = [];
        $this->transformFilters($resourceClass);

        return [array_values($this->filterServicesDefinition), $this->resource];
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

            if (false === $isCustomOperation = \is_array($operation)) {
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
