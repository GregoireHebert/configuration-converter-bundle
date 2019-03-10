<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Command;

use ApiPlatform\ConfigurationConverter\ConfigurationConverter;
use ApiPlatform\ConfigurationConverter\DataTransformers\XmlTransformer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConverterCommand extends Command
{
    protected static $defaultName = 'api:configuration:convert';

    private $xmlTransformer;
    private $defaultExportDir;

    public function __construct(XmlTransformer $xmlTransformer, string $defaultExportDir)
    {
        $this->xmlTransformer = $xmlTransformer;
        $this->defaultExportDir = $defaultExportDir;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Convert your configuration from annotation to yaml')
            ->addArgument('resource', InputArgument::REQUIRED, 'ApiResource FQCN. (App\\Entity\\Book)')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Format to convert to. xml(default), yaml or annotation', 'xml')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output the result in this directory (config/packages/api-platform)', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $io = new SymfonyStyle($input, $output);

        $resource = $input->getArgument('resource');
        $format = $input->getOption('format');
        $outputDirectory = $input->getOption('output') ?? $this->defaultExportDir;

        if (!\is_string($resource) || !\is_string($format)) {
            throw new \InvalidArgumentException('resource, format and output arguments must be strings (even empty ones).');
        }

        $io->note(sprintf('Converting resource: %s', $resource));

        try {
            $converter = new ConfigurationConverter($this->xmlTransformer);
            $content = $converter->convert($resource, $format, $outputDirectory);

            if ('' !== $outputDirectory) {
                $io->success(<<<TXT
Check your configuration in the $outputDirectory directory, and don't forget to configure API Platform to use it.
https://api-platform.com/docs/core/getting-started/#mapping-the-entities
TXT
                );
            }

            if ('' === $outputDirectory || $output->isVerbose()) {
                $io->success($content);
            }

            return 0;
        } catch (\Exception $e) {
            $io->writeln($e->getTraceAsString(), OutputInterface::VERBOSITY_VERBOSE);
            $io->error($e->getMessage());

            return 1;
        }
    }
}
