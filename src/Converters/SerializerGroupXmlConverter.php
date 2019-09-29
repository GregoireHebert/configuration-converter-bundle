<?php

declare(strict_types=1);

namespace ConfigurationConverter\Converters;

use ConfigurationConverter\Encoders\SerializerGroupXmlEncoder;
use ConfigurationConverter\Events\SerializerGroupConvertedEvent;
use ConfigurationConverter\Serializers\SerializerGroupXmlSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SerializerGroupXmlConverter implements ConverterInterface
{
    private $eventDispatcher;
    private $serializationGroupXmlEncoder;
    private $serializationGroupXmlSerializer;

    public function __construct(
        SerializerGroupXmlEncoder $serializationGroupXmlEncoder,
        SerializerGroupXmlSerializer $serializationGroupXmlSerializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->serializationGroupXmlEncoder = $serializationGroupXmlEncoder;
        $this->serializationGroupXmlSerializer = $serializationGroupXmlSerializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function support(string $format, ?string $type): bool
    {
        return 'xml' === strtolower(trim($format)) && ConfigurationConverter::CONVERT_GROUPS === $type;
    }

    public function convert(string $resourceClass): void
    {
        $serializerGroupsEncoded = $this->serializationGroupXmlEncoder->encode($resourceClass);

        if (!empty($serializerGroupsEncoded)) {
            $serializerGroupsSerialized = $this->serializationGroupXmlSerializer->serialize($serializerGroupsEncoded);
            $this->eventDispatcher->dispatch(SerializerGroupConvertedEvent::NAME, new SerializerGroupConvertedEvent($serializerGroupsSerialized));
        }
    }
}
