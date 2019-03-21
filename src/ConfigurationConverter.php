<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter;

use ApiPlatform\ConfigurationConverter\DataTransformers\ConfigurationConverterInterface;
use ApiPlatform\ConfigurationConverter\DataTransformers\XmlTransformer;

class ConfigurationConverter
{
    /**
     * @var array|ConfigurationConverterInterface[]
     */
    private $transformers = [];

    public function __construct(ConfigurationConverterInterface ...$transformers)
    {
        foreach ($transformers as $transformer) {
            $this->transformers[$transformer->getName()] = $transformer;
        }
    }

    public function convert(string $resourceClass, string $format = 'xml', string $exportPath): string
    {
        if (null === $transformer = $this->transformers[$format] ?? null) {
            throw new \InvalidArgumentException(sprintf(
                'You must specify a supported format (%s)',
                implode(', ', array_keys($this->transformers))
            ));
        }

        $shortName = (new \ReflectionClass($resourceClass))->getShortName();
        $newFormat = $transformer->transform($resourceClass);
        $extra = '';

        if ($transformer instanceof XmlTransformer && null !== $services = $transformer->getFiltersServiceDefinition()) {
            if ($exportPath) {
                $this->export("$shortName.services", $services, $exportPath);
            }

            $extra = <<<TXT
# config/packages/api-platform/$shortName.services.$format

$services
TXT;
        }

        return $exportPath ?
            $this->export($shortName, $newFormat, $exportPath) :
            <<<TXT
Check and paste this configuration:
# config/packages/api-platform/$shortName.$format

$newFormat
$extra
TXT;
    }

    /**
     * @throws \ReflectionException
     */
    protected function export(string $fileName, string $content, string $exportPath): string
    {
        if (!is_dir($exportPath) && !mkdir($exportPath, 0777, true)) {
            throw new \RuntimeException('Impossible to open or create the export directory');
        }

        $fileName = sprintf('%s/%s.xml', $exportPath, $fileName);
        $file = fopen($fileName, 'w');

        if (!\is_resource($file)) {
            throw new \RuntimeException("Impossible to open or create the file $fileName");
        }

        fwrite($file, $content);
        fclose($file);

        return $fileName;
    }
}
