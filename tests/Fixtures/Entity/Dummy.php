<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource
 */
class Dummy
{
    /**
     * @ApiProperty(identifier=true)
     */
    public $id;
}
