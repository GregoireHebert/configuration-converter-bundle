<?php

declare(strict_types=1);

namespace ConfigurationConverter\Serializers;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiResourceXmlSerializer implements ConfigurationSerializerInterface
{
    public function serialize(array $data): string
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new XmlEncoder()]);

        return (string) $serializer->encode(
            [
                '@xmlns' => 'https://api-platform.com/schema/metadata',
                '@xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                '@xsi:schemaLocation' => 'https://api-platform.com/schema/metadata https://api-platform.com/schema/metadata/metadata-2.0.xsd',
                'resource' => $data,
            ],
            'xml',
            [
                XmlEncoder::ROOT_NODE_NAME => 'resources',
                XmlEncoder::REMOVE_EMPTY_TAGS => true,
                XmlEncoder::AS_COLLECTION => true,
                XmlEncoder::FORMAT_OUTPUT => true,
            ]
        );
    }
}
