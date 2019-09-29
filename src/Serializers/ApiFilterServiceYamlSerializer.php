<?php

declare(strict_types=1);

namespace ConfigurationConverter\Serializers;

use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiFilterServiceYamlSerializer implements ConfigurationSerializerInterface
{
    public function serialize(array $data): string
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new YamlEncoder()]);

        return  (string) $serializer->encode(
            [
                'services' => $data,
            ],
            'yml',
            [
                'yaml_inline' => 6,
            ]
        );
    }
}
