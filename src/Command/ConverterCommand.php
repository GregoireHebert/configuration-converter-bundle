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
use Symfony\Component\Serializer\Annotation\Groups;

class ConverterCommand extends Command
{
    protected static $defaultName = 'configuration:convert';

    private $configurationConverter;
    private $defaultApiPlatformExportDir;
    private $defaultSerializerGroupExportDir;
    private $serializerGroupsEntitiesDirectories;
    private $resourceClassDirectories;
    /**
     * @var SymfonyStyle
     */
    private $io;
    private $reader;

    public function __construct(
        ConfigurationConverter $configurationConverter,
        Reader $reader,
        string $defaultApiPlatformExportDir,
        string $defaultSerializerGroupExportDir,
        array $serializerGroupsEntitiesDirectories,
        array $resourceClassDirectories
    ) {
        $this->configurationConverter = $configurationConverter;
        $this->reader = $reader;
        $this->defaultApiPlatformExportDir = $defaultApiPlatformExportDir;
        $this->defaultSerializerGroupExportDir = $defaultSerializerGroupExportDir;
        $this->serializerGroupsEntitiesDirectories = $serializerGroupsEntitiesDirectories;
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
            ->addOption('configurations', 'c', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Target the configuration type to be converted', [ConfigurationConverter::CONVERT_API_PLATFORM, ConfigurationConverter::CONVERT_GROUPS])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->io = new SymfonyStyle($input, $output);

        $resource = $input->getOption('resource');
        $format = $input->getOption('format');
        $apiPlatformOutputDirectory = $input->getOption('output') ?? $this->defaultApiPlatformExportDir;
        $serializerGroupOutputDirectory = $input->getOption('output') ?? $this->defaultSerializerGroupExportDir;
        $configurationList = (array) $input->getOption('configurations');

        if (!\is_string($format) || !\is_string($apiPlatformOutputDirectory)) {
            throw new \InvalidArgumentException('format and output options must be strings (even empty ones).');
        }

        try {
            if (\in_array(ConfigurationConverter::CONVERT_API_PLATFORM, $configurationList, true)) {
                $this->convert($resource, $format, ConfigurationConverter::CONVERT_API_PLATFORM, $this->resourceClassDirectories, $apiPlatformOutputDirectory, ApiResource::class);
            }

            if (\in_array(ConfigurationConverter::CONVERT_GROUPS, $configurationList, true)) {
                $this->convert($resource, $format, ConfigurationConverter::CONVERT_GROUPS, $this->serializerGroupsEntitiesDirectories, $serializerGroupOutputDirectory, Groups::class);
            }

            return 0;
        } catch (\Exception $e) {
            $this->io->writeln($e->getTraceAsString(), OutputInterface::VERBOSITY_VERBOSE);
            $this->io->error($e->getMessage());

            return 1;
        }
    }

    private function convert($resource, ?string $format, string $type, array $inputDirectories, ?string $outputDirectory, string $annotation): void
    {
        if (!\is_string($resource) || '' === $resource) {
            foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($inputDirectories) as $resourceClass => $reflectionClass) {
                if (null !== $this->reader->getClassAnnotation($reflectionClass, $annotation)) {
                    $this->io->note(sprintf('Converting resource: %s', $resourceClass));
                    foreach ($this->configurationConverter->convert($resourceClass, $format, $type, $outputDirectory) as $result) {
                        $this->io->success($result);
                    }
                }
            }
        } else {
            $this->io->note(sprintf('Converting resource: %s', $resource));
            foreach ($this->configurationConverter->convert($resource, $format, $type, $outputDirectory) as $result) {
                $this->io->success($result);
            }
        }
    }
}
