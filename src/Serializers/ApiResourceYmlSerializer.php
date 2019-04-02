<?php

declare(strict_types=1);

namespace ConfigurationConverter\Serializers;

use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiResourceYmlSerializer implements ConfigurationSerializerInterface
{
    public function serialize(array $data): string
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new YamlEncoder()]);

        return (string) $serializer->encode(
            $data,
            'yml',
            [
                'yaml_inline' => 6,
            ]
        );
    }
}
