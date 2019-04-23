<?php

declare(strict_types=1);

namespace ConfigurationConverter\Writers;

use ConfigurationConverter\Events\SerializerGroupConvertedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class SerializerGroupWriter implements WriterInterface
{
    /**
     * @var array|SerializerGroupConvertedEvent[]
     */
    protected $groups = [];

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->addListener(SerializerGroupConvertedEvent::NAME, function (SerializerGroupConvertedEvent $event): void {
            $this->groups[] = $event;
        });
    }

    public function init(): void
    {
        $this->groups = [];
    }
}
