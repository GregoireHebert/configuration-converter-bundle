<?php

declare(strict_types=1);

namespace ConfigurationConverter\Writers;

use ConfigurationConverter\Events\ApiFilterConvertedEvent;
use ConfigurationConverter\Events\ApiResourceConvertedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class ApiPlatformWriter implements WriterInterface
{
    /**
     * @var array|ApiResourceConvertedEvent[]
     */
    protected $resources = [];
    /**
     * @var array|ApiFilterConvertedEvent[]
     */
    protected $filters = [];

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->addListener(ApiResourceConvertedEvent::NAME, function (ApiResourceConvertedEvent $event): void {
            $this->resources[] = $event;
        });

        $eventDispatcher->addListener(ApiFilterConvertedEvent::NAME, function (ApiFilterConvertedEvent $event): void {
            $this->filters[] = $event;
        });
    }

    public function init(): void
    {
        $this->resources = [];
        $this->filters = [];
    }
}
