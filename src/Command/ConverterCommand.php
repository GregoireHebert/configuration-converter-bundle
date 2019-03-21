<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Command;

use ApiPlatform\ConfigurationConverter\ConfigurationConverter;
use ApiPlatform\ConfigurationConverter\DataTransformers\XmlTransformer;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Util\ReflectionClassRecursiveIterator;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConverterCommand extends Command
{
    protected static $defaultName = 'api:configuration:convert';

    private $xmlTransformer;
    private $defaultExportDir;
    private $resourceClassDirectories;
    /**
     * @var SymfonyStyle
     */
    private $io;
    private $reader;

    public function __construct(XmlTransformer $xmlTransformer, Reader $reader, string $defaultExportDir, array $resourceClassDirectories)
    {
        $this->xmlTransformer = $xmlTransformer;
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

        if (!\is_string($resource) || '' === $resource) {
            foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($this->resourceClassDirectories) as $resource => $reflectionClass) {
                if (null !== $this->reader->getClassAnnotation($reflectionClass, ApiResource::class)) {
                    $this->io->note(sprintf('Converting resource: %s', $resource));
                    $this->convertResource($resource, $format, $outputDirectory);
                }
            }
        } else {
            $this->io->note(sprintf('Converting resource: %s', $resource));
            $this->convertResource($resource, $format, $outputDirectory);
        }

        return 0;
    }

    private function convertResource(string $resource, string $format, string $outputDirectory): int
    {
        try {
            $converter = new ConfigurationConverter($this->xmlTransformer);
            $content = $converter->convert($resource, $format, $outputDirectory);

            if ('' !== $outputDirectory) {
                $this->io->success(<<<TXT
Check your configuration in the $outputDirectory directory, and don't forget to configure API Platform to use it.
https://api-platform.com/docs/core/getting-started/#mapping-the-entities
TXT
                );
            }

            if ('' === $outputDirectory || $this->io->isVerbose()) {
                $this->io->success($content);
            }

            return 0;
        } catch (\Exception $e) {
            $this->io->writeln($e->getTraceAsString(), OutputInterface::VERBOSITY_VERBOSE);
            $this->io->error($e->getMessage());

            return 1;
        }
    }
}
