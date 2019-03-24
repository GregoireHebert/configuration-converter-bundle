<?php

declare(strict_types=1);

namespace ConfigurationConverter\Events;

class ApiResourceConvertedEvent extends AbstractConvertedEvent
{
    public const NAME = 'configuration.api_resource.converted';
}
