<?php

declare(strict_types=1);

namespace ConfigurationConverter\Converters;

use ConfigurationConverter\Encoders\SerializerGroupYamlEncoder;
use ConfigurationConverter\Events\SerializerGroupConvertedEvent;
use ConfigurationConverter\Serializers\SerializerGroupYamlSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SerializerGroupYamlConverter implements ConverterInterface
{
    private $eventDispatcher;
    private $serializationGroupYamlEncoder;
    private $serializationGroupYamlSerializer;

    public function __construct(
        SerializerGroupYamlEncoder $serializationGroupYamlEncoder,
        SerializerGroupYamlSerializer $serializationGroupYamlSerializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->serializationGroupYamlEncoder = $serializationGroupYamlEncoder;
        $this->serializationGroupYamlSerializer = $serializationGroupYamlSerializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function support(string $format, array $configurations): bool
    {
        return ('yml' === strtolower(trim($format)) || 'yaml' === strtolower(trim($format))) && in_array(ConfigurationConverter::CONVERT_GROUPS, $configurations, true);
    }

    public function convert(string $resourceClass): void
    {
        $serializerGroupsEncoded = $this->serializationGroupYamlEncoder->encode($resourceClass);

        if (!empty($serializerGroupsEncoded)) {
            $serializerGroupsSerialized = $this->serializationGroupYamlSerializer->serialize($serializerGroupsEncoded);
            $this->eventDispatcher->dispatch(SerializerGroupConvertedEvent::NAME, new SerializerGroupConvertedEvent($serializerGroupsSerialized));
        }
    }
}
