<?php

declare(strict_types=1);

namespace ConfigurationConverter\Converters;

use ConfigurationConverter\Encoders\ApiFilterYmlEncoder;
use ConfigurationConverter\Encoders\ApiResourceYmlEncoder;
use ConfigurationConverter\Events\ApiFilterConvertedEvent;
use ConfigurationConverter\Events\ApiResourceConvertedEvent;
use ConfigurationConverter\Serializers\ApiFilterServiceYmlSerializer;
use ConfigurationConverter\Serializers\ApiResourceYmlSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ApiPlatformYmlConverter implements ConverterInterface
{
    private $apiResourceYmlSerializer;
    private $apiFilterYmlSerializer;
    private $apiResourceYmlEncoder;
    private $apiFilterYmlEncoder;
    private $eventDispatcher;

    public function __construct(
        ApiResourceYmlSerializer $apiResourceYmlSerializer,
        ApiFilterServiceYmlSerializer $apiFilterYmlSerializer,
        ApiResourceYmlEncoder $apiResourceYmlEncoder,
        ApiFilterYmlEncoder $apiFilterYmlEncoder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->apiResourceYmlSerializer = $apiResourceYmlSerializer;
        $this->apiFilterYmlSerializer = $apiFilterYmlSerializer;
        $this->apiResourceYmlEncoder = $apiResourceYmlEncoder;
        $this->apiFilterYmlEncoder = $apiFilterYmlEncoder;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function support(string $format, array $configurations): bool
    {
        return ('yml' === strtolower(trim($format)) || 'yaml' === strtolower(trim($format))) && in_array(ConfigurationConverter::CONVERT_API_PLATFORM, $configurations, true);
    }

    public function convert(string $resourceClass): void
    {
        $apiResourceEncoded = $this->apiResourceYmlEncoder->encode($resourceClass);
        [$apiFilterEncoded, $apiResourceEncoded] = $this->apiFilterYmlEncoder->fromEncodedApiResource($apiResourceEncoded)->encode($resourceClass);

        if (!empty($apiResourceEncoded)) {
            $apiResourceSerialized = $this->apiResourceYmlSerializer->serialize($apiResourceEncoded);
            $this->eventDispatcher->dispatch(ApiResourceConvertedEvent::NAME, new ApiResourceConvertedEvent($apiResourceSerialized));
        }

        if (!empty($apiFilterEncoded)) {
            $apiFilterSerialized = $this->apiFilterYmlSerializer->serialize($apiFilterEncoded);
            $this->eventDispatcher->dispatch(ApiFilterConvertedEvent::NAME, new ApiFilterConvertedEvent($apiFilterSerialized));
        }
    }
}
