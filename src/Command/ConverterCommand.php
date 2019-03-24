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
    protected static $defaultName = 'api:configuration:convert';

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
            ->setDescription('Convert your configuration from annotation to yaml')
            ->addOption('resource', 'r', InputOption::VALUE_OPTIONAL, 'ApiResource FQCN. (App\\Entity\\Book)')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Format to convert to. xml(default), yaml or annotation', 'xml')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output the result in this directory (config/packages/api-platform)', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->io = new SymfonyStyle($input, $output);

        $resource = $input->getOption('resource');
        $format = $input->getOption('format');
        $outputDirectory = $input->getOption('output') ?? $this->defaultExportDir;

        if (!\is_string($format) || !\is_string($outputDirectory)) {
            throw new \InvalidArgumentException('format and output options must be strings (even empty ones).');
        }

        try {
            if (!\is_string($resource) || '' === $resource) {
                foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($this->resourceClassDirectories) as $resource => $reflectionClass) {
                    if (null !== $this->reader->getClassAnnotation($reflectionClass, ApiResource::class)) {
                        $this->io->note(sprintf('Converting resource: %s', $resource));
                        foreach ($this->configurationConverter->convert($resource, $format, $outputDirectory) as $result) {
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

            return 0;
        } catch (\Exception $e) {
            $this->io->writeln($e->getTraceAsString(), OutputInterface::VERBOSITY_VERBOSE);
            $this->io->error($e->getMessage());

            return 1;
        }
    }
}
