<?php

declare(strict_types=1);

namespace ConfigurationConverter\Serializers;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiFilterServiceXmlSerializer implements ConfigurationSerializerInterface
{
    public function serialize(array $data): string
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new XmlEncoder()]);

        return  (string) $serializer->encode(
            [
                '@xmlns' => 'http://symfony.com/schema/dic/services',
                '@xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                '@xsi:schemaLocation' => 'http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd',
                'services' => ['service' => $data],
            ],
            'xml',
            [
                XmlEncoder::ROOT_NODE_NAME => 'container',
                XmlEncoder::REMOVE_EMPTY_TAGS => true,
                XmlEncoder::AS_COLLECTION => true,
                XmlEncoder::FORMAT_OUTPUT => true,
            ]
        );
    }
}
