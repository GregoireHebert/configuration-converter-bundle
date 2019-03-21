<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get"={ "attributes"={"filters"={"tag.name_filter", "unknown.filterName"}} },
 *         "custom"={ "method"="POST", "attributes"={"filters"={"unknown.filterName"}} }
 *     }
 * )
 * @ApiFilter(OrderFilter::class, properties={"name"={ "nulls_comparison"=OrderFilter::NULLS_SMALLEST, "default_direction"="DESC" }})
 */
class Tag
{
    /**
     * var string.
     *
     * @ApiProperty(identifier=true)
     */
    public $id;
    /**
     * var string.
     */
    public $name;
}
