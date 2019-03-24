<?php

declare(strict_types=1);

namespace ConfigurationConverter\Converters;

use ConfigurationConverter\Writers\WriterInterface;

final class ConfigurationConverter
{
    /**
     * @var ConverterInterface[]
     */
    private $converters;
    /**
     * @var WriterInterface[]
     */
    private $writers;

    public function __construct($converters = [], $writers = [])
    {
        $this->converters = $converters;
        $this->writers = $writers;
    }

    public function convert(string $resourceClass, string $format = 'xml', string $exportPath = ''): iterable
    {
        foreach ($this->writers as $writer) {
            $writer->init();
        }

        foreach ($this->converters as $converter) {
            if ($converter->support($format)) {
                $converter->convert($resourceClass);
            }
        }

        $shortName = (new \ReflectionClass($resourceClass))->getShortName();
        foreach ($this->writers as $writer) {
            if (null !== $result = $writer->write($shortName, $format, $exportPath)) {
                yield $result;
            }
        }
    }
}