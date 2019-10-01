<?php

declare(strict_types=1);

namespace ConfigurationConverter\Encoders;

use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\AbstractFilter as MongoDbOdmAbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Symfony\Component\HttpFoundation\Request;

final class ApiFilterXmlEncoder extends AbstractApiPlatformFilterEncoder implements ConfigurationEncoderInterface
{
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
                '@parent' => self::FILTERS_SERVICES_ID[$class = \get_class($filter)] ?? $class,
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
