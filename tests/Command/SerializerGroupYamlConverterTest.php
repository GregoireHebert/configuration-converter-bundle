<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Command;

use ConfigurationConverter\Command\ConverterCommand;
use ConfigurationConverter\Converters\ConfigurationConverter;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class SerializerGroupYamlConverterTest extends KernelTestCase
{
    /**
     * @var Application
     */
    protected static $application;
    /**
     * @var Command
     */
    protected static $command;
    /**
     * @var CommandTester
     */
    protected static $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        self::$application = new Application(self::$kernel);
    }

    public function testCommandLoaded(): void
    {
        self::$command = self::$application->find('configuration:convert');
        self::$commandTester = new CommandTester(self::$command);

        $this->assertInstanceOf(ConverterCommand::class, self::$command);
    }

    public function testCommandWithGoodConfigurationsArgument(): void
    {
        self::$commandTester->execute(
            [
                'command' => self::$command->getName(),
                '--resource' => 'ConfigurationConverter\Test\Fixtures\Entity\Book',
                '--configurations' => [ConfigurationConverter::CONVERT_GROUPS],
                '--format' => 'yaml',
                '-vvv' => '',
            ]
        );

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] Converting resource:', $output);
    }

    public function testCommandWithOutputArgument(): void
    {
        $output = self::$kernel->getProjectDir().'/config/packages/serialization/';

        self::$commandTester->execute(
            [
                'command' => self::$command->getName(),
                '--resource' => 'ConfigurationConverter\Test\Fixtures\Entity\Book',
                '--configurations' => [ConfigurationConverter::CONVERT_GROUPS],
                '--format' => 'yaml',
                '--output' => $output,
                '-vvv' => '',
            ]
        );

        $this->assertFileExists($output.'Book.yaml');
    }
}
