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

class ApiPlatformYmlConverterTest extends KernelTestCase
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
                '--configurations' => [ConfigurationConverter::CONVERT_API_PLATFORM],
                '--format' => 'yml',
                '-vvv' => '',
            ]
        );

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] Converting resource:', $output);
    }

    public function testCommandWithoutOutputArgument(): void
    {
        self::$commandTester->execute(
            ['command' => self::$command->getName(), '--resource' => 'ConfigurationConverter\Test\Fixtures\Entity\Book', '--format' => 'yml']
        );

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] Converting resource:', $output);
        $this->assertStringContainsString('ConfigurationConverter\Test\Fixtures\Entity\Book', $output);
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);

        self::$commandTester->execute(
            ['command' => self::$command->getName(), '--resource' => 'ConfigurationConverter\Test\Fixtures\Entity\Tag', '--format' => 'yml']
        );
        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);

        self::$commandTester->execute(
            ['command' => self::$command->getName(), '--resource' => 'ConfigurationConverter\Test\Fixtures\Entity\Dummy', '--format' => 'yml']
        );
        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);
    }

    public function testYmlNonSpecifiedResourcesOutput(): void
    {
        $output = self::$kernel->getProjectDir().'/config/packages/api-platform/';
        $expected = self::$kernel->getProjectDir().'/config/packages/expected/';

        $filesystem = new Filesystem();
        $filesystem->remove($output);

        self::$commandTester->execute([
            'command' => self::$command->getName(),
            '--output' => $output,
            '--format' => 'yml',
        ]);

        $this->assertFileNotExists($output.'Dummy.services.yml');
        $this->assertFileNotExists($output.'NotAResource.yml');

        $this->assertFileEquals($expected.'Book.yml', $output.'Book.yml');
        $this->assertFileEquals($expected.'Book.services.yml', $output.'Book.services.yml');
        $this->assertFileEquals($expected.'Tag.yml', $output.'Tag.yml');
        $this->assertFileEquals($expected.'Tag.services.yml', $output.'Tag.services.yml');
        $this->assertFileEquals($expected.'Dummy.yml', $output.'Dummy.yml');
    }

    public function testYmlNonSpecifiedResourcesWithoutOutput(): void
    {
        self::$commandTester->execute([
            'command' => self::$command->getName(),
            '--format' => 'yml',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Book.yml', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Book.services.yml', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Dummy.yml', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Tag.yml', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Tag.services.yml', $output);
    }
}
