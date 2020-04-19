<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Resource;

class ResourceImports implements \IteratorAggregate, \Countable
{
    /**
     * @var ResourceImport[]
     */
    private array $resources = [];

    /**
     * @return ResourceImport[]
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->resources);
    }

    public function addResource(ResourceImport $resource): void
    {
        $this->resources[] = $resource;
    }

    public function count(): int
    {
        return \count($this->resources);
    }
}
