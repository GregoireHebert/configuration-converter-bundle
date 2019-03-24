<?php

declare(strict_types=1);

namespace ConfigurationConverter\Writers;

interface WriterInterface
{
    /**
     * Set and reset the values.
     */
    public function init(): void;

    /**
     * Writes the converted into a file or return the string value to be displayed through CLI.
     */
    public function write(string $shortName, string $format, string $exportPath): ?string;
}
