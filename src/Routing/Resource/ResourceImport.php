<?php

declare(strict_types=1);

namespace ConfigurationConverter\Routing\Resource;

class ResourceImport
{
    private string $name;

    // Route specific
    private ?string $path = null;
    private ?string $host;
    private array $schemes;
    private array $methods;
    private array $defaults;
    private array $requirements;
    private array $options;
    private ?string $condition;

    // Import specific
    private ?string $resource = null;
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
        return null !== $this->resource;
    }

    public function isRoute(): bool
    {
        return null !== $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getSchemes(): array
    {
        return $this->schemes;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function trailingSlashOnRoot(): bool
    {
        return $this->trailingSlashOnRoot;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getExclude(): ?array
    {
        return $this->exclude;
    }
}
