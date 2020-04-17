<?php

declare(strict_types=1);

namespace ConfigurationConverter\Command;

use Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouteCollection;

class RouteFileConverterCommand extends Command
{
    protected static $defaultName = 'configuration:convert:route-file';

    private const INPUT_FORMATS = [
        'xml',
        'yml',
        'yaml',
        'annotations',
    ];

    private const OUTPUT_FORMATS = [
        'xml' => 'xml',
        'yaml' => 'yaml',
        'php' => 'php',
        'fluent' => 'php',
    ];

    private const DEFAULT_OUTPUT_FORMAT = 'php';

    private SymfonyStyle $io;
    private string $projectDir;
    private DelegatingLoader $routingLoader;
    private array $converters = [];

    public function __construct(string $projectDir, iterable $converters, DelegatingLoader $routingLoader)
    {
        $this->projectDir = $projectDir;
        $this->routingLoader = $routingLoader;
        foreach ($converters as $converter) {
            $this->addConverter($converter);
        }
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'List of files you want to convert')
            ->addOption('output-format', 'o', InputOption::VALUE_OPTIONAL, 'The format you want to convert the files to. Available formats: '.implode(', ', self::OUTPUT_FORMATS), self::DEFAULT_OUTPUT_FORMAT)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $outputFormat = $input->getOption('output-format');

        $file = ltrim(str_replace('\\', '/', $input->getArgument('file')), '/');
        $filePath = $this->projectDir.'/'.$file;
        if (!file_exists($filePath)) {
            $this->io->error(sprintf('File %s does not exist.', $filePath));

            return 1;
        }

        $this->convertFile($filePath, $outputFormat);

        return 0;
    }

    private function convertFile(string $file, string $outputFormat): void
    {
        /** @var RouteCollection $collection */
        $collection = $this->routingLoader->load($file);

        $pathinfo = pathinfo($file);

        $outputExt = self::OUTPUT_FORMATS[$outputFormat];

        $outputFile = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.'.$outputExt;

        switch ($outputFormat) {
            case 'fluent':
                $fileContent = $this->convertToFluent($collection);
                break;
            default:
                $this->io->error(sprintf(
                    'Format %s not implemented yet.',
                    $outputFormat
                ));

                return;
        }

        if (file_exists($outputFile)) {
            if (!$this->io->confirm(sprintf('File <info>%s</info> already exists. Overwrite?', $outputFile))) {
                return;
            }
        }

        file_put_contents($outputFile, $fileContent);

        $this->io->success(sprintf('Written %s', $pathinfo['filename'].'.'.$outputExt));
    }
}
