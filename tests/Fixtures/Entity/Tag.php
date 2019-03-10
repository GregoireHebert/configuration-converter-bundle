<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get"={ "attributes"={"filters"={"tag.name_filter"}} }
 *     }
 * )
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
