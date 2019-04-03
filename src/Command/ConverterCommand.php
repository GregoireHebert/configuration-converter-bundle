<?php

declare(strict_types=1);

namespace ConfigurationConverter\Command;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Util\ReflectionClassRecursiveIterator;
use ConfigurationConverter\Converters\ConfigurationConverter;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConverterCommand extends Command
{
    public const CONVERT_API_PLATFORM = 'api_platform';

    protected static $defaultName = 'configuration:convert';

    private $configurationConverter;
    private $defaultExportDir;
    private $resourceClassDirectories;
    /**
     * @var SymfonyStyle
     */
    private $io;
    private $reader;

    public function __construct(ConfigurationConverter $configurationConverter, Reader $reader, string $defaultExportDir, array $resourceClassDirectories)
    {
        $this->configurationConverter = $configurationConverter;
        $this->reader = $reader;
        $this->defaultExportDir = $defaultExportDir;
        $this->resourceClassDirectories = $resourceClassDirectories;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Convert your configuration from annotation to xml or yaml')
            ->addOption('resource', 'r', InputOption::VALUE_REQUIRED, 'ApiResource FQCN. (App\\Entity\\Book)')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Format to convert to. xml(default) or yaml', 'xml')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output the result in the default directory (config/packages/api-platform) or in the specified one.', '')
            ->addOption('configurations', 'c', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Target the configuration type to be converted', [self::CONVERT_API_PLATFORM])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->io = new SymfonyStyle($input, $output);

        $resource = $input->getOption('resource');
        $format = $input->getOption('format');
        $outputDirectory = $input->getOption('output') ?? $this->defaultExportDir;
        $configurationList = array_flip((array) $input->getOption('configurations'));

        if (!\is_string($format) || !\is_string($outputDirectory)) {
            throw new \InvalidArgumentException('format and output options must be strings (even empty ones).');
        }

        try {
            if (isset($configurationList[self::CONVERT_API_PLATFORM])) {
                $this->convertApiPlatform($resource, $format, $outputDirectory);
            }

            return 0;
        } catch (\Exception $e) {
            $this->io->writeln($e->getTraceAsString(), OutputInterface::VERBOSITY_VERBOSE);
            $this->io->error($e->getMessage());

            return 1;
        }
    }

    private function convertApiPlatform($resource, ?string $format, ?string $outputDirectory): void
    {
        if (!\is_string($resource) || '' === $resource) {
            foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($this->resourceClassDirectories) as $resourceClass => $reflectionClass) {
                if (null !== $this->reader->getClassAnnotation($reflectionClass, ApiResource::class)) {
                    $this->io->note(sprintf('Converting resource: %s', $resourceClass));
                    foreach ($this->configurationConverter->convert($resourceClass, $format, $outputDirectory) as $result) {
                        $this->io->success($result);
                    }
                }
            }
        } else {
            $this->io->note(sprintf('Converting resource: %s', $resource));
            foreach ($this->configurationConverter->convert($resource, $format, $outputDirectory) as $result) {
                $this->io->success($result);
            }
        }
    }
}
