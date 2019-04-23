<?php

declare(strict_types=1);

namespace ConfigurationConverter\Events;

class SerializerGroupConvertedEvent extends AbstractConvertedEvent
{
    public const NAME = 'configuration.serialization_group.converted';
}
