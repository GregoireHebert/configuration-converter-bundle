<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Test\Command;

use ApiPlatform\ConfigurationConverter\Command\ConverterCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AnnotationToXmlConverterTest extends KernelTestCase
{
    /**
     * @var Application
     */
    protected static $application;
    protected static $command;
    protected static $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        self::$application = new Application(self::$kernel);
    }

    public function testCommandLoaded(): void
    {
        self::$command = self::$application->find('api:configuration:convert');
        self::$commandTester = new CommandTester(self::$command);

        $this->assertInstanceOf(ConverterCommand::class, self::$command);
    }

    public function testCommandWithoutArgument(): void
    {
        self::$commandTester->execute(
            ['command' => self::$command->getName(), 'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book']
        );

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] Converting resource: ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book', $output);
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);

        self::$commandTester->execute(
            ['command' => self::$command->getName(), 'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Tag']
        );
        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);

        self::$commandTester->execute(
            ['command' => self::$command->getName(), 'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Dummy']
        );
        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);
    }

    public function testCommandWithBadFormatArgument(): void
    {
        self::$commandTester->execute([
            'command' => self::$command->getName(),
            'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book',
            '--format' => 'badformat',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[ERROR] You must specify a supported format', $output);
    }

    public function testCommandWithXmlFormatArgument(): void
    {
        self::$commandTester->execute([
            'command' => self::$command->getName(),
            'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book',
            '--format' => 'xml',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);
    }

    public function testCommandWithoutPermissionOutputArgument(): void
    {
        chmod(self::$kernel->getProjectDir().'/../../Fixtures/forbidenDir', 0555);
        chmod(self::$kernel->getProjectDir().'/../../Fixtures/forbidenDir/Book.xml', 0444);

        self::$commandTester->execute([
            'command' => self::$command->getName(),
            'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book',
            '--output' => self::$kernel->getProjectDir().'/../../Fixtures/forbidenDir',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('Permission denied', $output);

        self::$commandTester->execute([
            'command' => self::$command->getName(),
            'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book',
            '--output' => self::$kernel->getProjectDir().'/../../Fixtures/forbidenDir/cannotcreatedir',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('Permission denied', $output);
    }

    public function testCommandWithTmpOutputArgument(): void
    {
        self::$commandTester->execute([
            'command' => self::$command->getName(),
            'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book',
            '--output' => '/tmp',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check your configuration in the /tmp directory, and don\'t forget to configure API Platform to use it', $output);
    }
}
