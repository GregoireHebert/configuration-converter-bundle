<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Resource;

class ResourceImport
{
    private string $name;

    // Route specific
    private ?string $path;
    private ?string $host;
    private array $schemes;
    private array $methods;
    private array $defaults;
    private array $requirements;
    private array $options;
    private ?string $condition;

    // Import specific
    private ?string $resource;
    private ?string $prefix;
    private bool $trailingSlashOnRoot;
    private ?string $type;
    private ?array $exclude;

    private function __construct()
    {
    }

    public static function fromRoute(
        string $name,
        $path,
        array $defaults,
        array $requirements,
        array $options,
        ?string $host,
        array $schemes,
        array $methods,
        ?string $condition
    ) {
        $self = new self();

        $self->name = $name;
        $self->path = $path;
        $self->defaults = $defaults;
        $self->requirements = $requirements;
        $self->options = $options;
        $self->host = $host;
        $self->schemes = $schemes;
        $self->methods = $methods;
        $self->condition = $condition;

        return $self;
    }

    public static function fromImport(
        string $name,
        $resource,
        $type,
        string $prefix,
        array $defaults,
        array $requirements,
        array $options,
        string $host,
        $condition,
        array $schemes,
        array $methods,
        bool $trailingSlashOnRoot,
        $exclude
    ) {
        $self = new self();

        $self->name = $name;
        $self->resource = $resource;
        $self->type = $type;
        $self->prefix = $prefix;
        $self->defaults = $defaults;
        $self->requirements = $requirements;
        $self->options = $options;
        $self->host = $host;
        $self->condition = $condition;
        $self->schemes = $schemes;
        $self->methods = $methods;
        $self->trailingSlashOnRoot = $trailingSlashOnRoot;
        $self->exclude = $exclude;

        return $self;
    }
    public function isImport(): bool
    {
        return $this->resource !== null;
    }

    public function isRoute(): bool
    {
        return $this->path !== null;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
