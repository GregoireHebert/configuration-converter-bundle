<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Fixtures\App\src\Entity;

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
