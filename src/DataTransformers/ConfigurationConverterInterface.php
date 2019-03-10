<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\DataTransformers;

interface ConfigurationConverterInterface
{
    public function transform(string $resourceClass): string;

    public function getName(): string;
}
