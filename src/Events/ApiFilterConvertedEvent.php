<?php

declare(strict_types=1);

namespace ConfigurationConverter\Events;

class ApiFilterConvertedEvent extends AbstractConvertedEvent
{
    public const NAME = 'configuration.api_filter.converted';
}
