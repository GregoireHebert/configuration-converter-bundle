<?php

declare(strict_types=1);

namespace ConfigurationConverter\Events;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractConvertedEvent extends Event
{
    protected $data;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
