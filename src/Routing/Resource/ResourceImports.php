<?php

namespace ConfigurationConverter\Routing\Loader;

use ConfigurationConverter\Routing\Resource\ResourceImport;

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
