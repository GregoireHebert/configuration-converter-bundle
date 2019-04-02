<?php

declare(strict_types=1);

namespace ConfigurationConverter\Encoders;

use ApiPlatform\Core\Api\FilterLocatorTrait;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\BooleanFilter as MongoDbOdmBooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\DateFilter as MongoDbOdmDateFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\ExistsFilter as MongoDbOdmExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\NumericFilter as MongoDbOdmNumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\OrderFilter as MongoDbOdmOrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\RangeFilter as MongoDbOdmRangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\SearchFilter as MongoDbOdmSearchFilter;
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

abstract class AbstractApiPlatformFilterEncoder
{
    use FilterLocatorTrait { getFilter as protected; }

    protected const FILTERS_SERVICES_ID = [
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

    protected $resourceMetadataFactory;
    protected $propertyMetadataFactory;
    protected $propertyNameCollectionFactory;
    protected $resourceFilterMetadataFactory;
    protected $filterServicesDefinition;
    protected $resource = [];

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
}
