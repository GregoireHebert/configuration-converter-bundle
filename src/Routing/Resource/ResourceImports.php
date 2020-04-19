<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Converter\Loader;

use ConfigurationConverter\Routing\Converter\Resource\ResourceImport;

class ResourceImports implements \IteratorAggregate
{
    /**
     * @var ResourceImport[]
     */
    private array $resources;

    /**
     * @return ResourceImport[]
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->resources);
    }
}
