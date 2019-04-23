<?php

declare(strict_types=1);

namespace ConfigurationConverter\Converters;

use ConfigurationConverter\Encoders\ApiFilterXmlEncoder;
use ConfigurationConverter\Encoders\ApiResourceXmlEncoder;
use ConfigurationConverter\Events\ApiFilterConvertedEvent;
use ConfigurationConverter\Events\ApiResourceConvertedEvent;
use ConfigurationConverter\Serializers\ApiFilterServiceXmlSerializer;
use ConfigurationConverter\Serializers\ApiResourceXmlSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ApiPlatformXmlConverter implements ConverterInterface
{
    private $apiResourceXmlSerializer;
    private $apiFilterXmlSerializer;
    private $apiResourceXmlEncoder;
    private $apiFilterXmlEncoder;
    private $eventDispatcher;

    public function __construct(
        ApiResourceXmlSerializer $apiResourceXmlSerializer,
        ApiFilterServiceXmlSerializer $apiFilterXmlSerializer,
        ApiResourceXmlEncoder $apiResourceXmlEncoder,
        ApiFilterXmlEncoder $apiFilterXmlEncoder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->apiResourceXmlSerializer = $apiResourceXmlSerializer;
        $this->apiFilterXmlSerializer = $apiFilterXmlSerializer;
        $this->apiResourceXmlEncoder = $apiResourceXmlEncoder;
        $this->apiFilterXmlEncoder = $apiFilterXmlEncoder;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function support(string $format, array $configurations): bool
    {
        return 'xml' === strtolower(trim($format)) && in_array(ConfigurationConverter::CONVERT_API_PLATFORM, $configurations, true);
    }

    public function convert(string $resourceClass): void
    {
        $apiResourceEncoded = $this->apiResourceXmlEncoder->encode($resourceClass);
        [$apiFilterEncoded, $apiResourceEncoded] = $this->apiFilterXmlEncoder->fromEncodedApiResource($apiResourceEncoded)->encode($resourceClass);

        if (!empty($apiResourceEncoded)) {
            $apiResourceSerialized = $this->apiResourceXmlSerializer->serialize($apiResourceEncoded);
            $this->eventDispatcher->dispatch(ApiResourceConvertedEvent::NAME, new ApiResourceConvertedEvent($apiResourceSerialized));
        }

        if (!empty($apiFilterEncoded)) {
            $apiFilterSerialized = $this->apiFilterXmlSerializer->serialize($apiFilterEncoded);
            $this->eventDispatcher->dispatch(ApiFilterConvertedEvent::NAME, new ApiFilterConvertedEvent($apiFilterSerialized));
        }
    }
}
