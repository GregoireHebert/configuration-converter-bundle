<?php

declare(strict_types=1);

namespace ConfigurationConverter\Encoders;

interface ConfigurationEncoderInterface
{
    /**
     * Return the configuration in an array ready to be serialized.
     */
    public function encode(string $resourceClass): array;
}
