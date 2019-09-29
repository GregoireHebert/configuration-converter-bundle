<?php

declare(strict_types=1);

namespace ConfigurationConverter\Converters;

use ConfigurationConverter\Encoders\ApiFilterYamlEncoder;
use ConfigurationConverter\Encoders\ApiResourceYamlEncoder;
use ConfigurationConverter\Events\ApiFilterConvertedEvent;
use ConfigurationConverter\Events\ApiResourceConvertedEvent;
use ConfigurationConverter\Serializers\ApiFilterServiceYamlSerializer;
use ConfigurationConverter\Serializers\ApiResourceYamlSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ApiPlatformYamlConverter implements ConverterInterface
{
    private $apiResourceYmlSerializer;
    private $apiFilterYmlSerializer;
    private $apiResourceYmlEncoder;
    private $apiFilterYmlEncoder;
    private $eventDispatcher;

    public function __construct(
        ApiResourceYamlSerializer $apiResourceYmlSerializer,
        ApiFilterServiceYamlSerializer $apiFilterYmlSerializer,
        ApiResourceYamlEncoder $apiResourceYmlEncoder,
        ApiFilterYamlEncoder $apiFilterYmlEncoder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->apiResourceYmlSerializer = $apiResourceYmlSerializer;
        $this->apiFilterYmlSerializer = $apiFilterYmlSerializer;
        $this->apiResourceYmlEncoder = $apiResourceYmlEncoder;
        $this->apiFilterYmlEncoder = $apiFilterYmlEncoder;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function support(string $format, ?string $type): bool
    {
        return ('yml' === strtolower(trim($format)) || 'yaml' === strtolower(trim($format))) && ConfigurationConverter::CONVERT_API_PLATFORM === $type;
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
