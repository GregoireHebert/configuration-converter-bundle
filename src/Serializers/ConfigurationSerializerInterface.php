<?php

declare(strict_types=1);

namespace ConfigurationConverter\Serializers;

interface ConfigurationSerializerInterface
{
    /**
     * The serialized configuration.
     */
    public function serialize(array $data): string;
}
