<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Test\Command;

use ApiPlatform\ConfigurationConverter\Command\ConverterCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Util\XmlUtils;
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
        $this->assertStringContainsString('[NOTE] Converting resource:', $output);
        $this->assertStringContainsString('ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book', $output);
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
        chmod(self::$kernel->getProjectDir().'/../forbidenDir', 0555);
        chmod(self::$kernel->getProjectDir().'/../forbidenDir/Book.xml', 0444);

        self::$commandTester->execute([
            'command' => self::$command->getName(),
            'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book',
            '--output' => self::$kernel->getProjectDir().'/../forbidenDir',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('Permission denied', $output);

        self::$commandTester->execute([
            'command' => self::$command->getName(),
            'resource' => 'ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\Book',
            '--output' => self::$kernel->getProjectDir().'/../forbidenDir/cannotcreatedir',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('Permission denied', $output);
    }

    public function testXmlResourceOutput(): void
    {
        foreach (['Book', 'Tag', 'Dummy'] as $entityName) {
            self::$commandTester->execute([
                'command' => self::$command->getName(),
                'resource' => "ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity\\$entityName",
                '--output' => self::$kernel->getProjectDir() . '/config/packages/api-platform/',
            ]);
        }

        $fixtures = self::$kernel->getProjectDir().'/config/packages/api-platform/';
        $resourceSchema =  self::$kernel->getProjectDir().'/../../../vendor/api-platform/core/src/Metadata/schema/metadata.xsd';
        $servicesSchema =  self::$kernel->getProjectDir().'/../../../vendor/symfony/dependency-injection/Loader/schema/dic/services/services-1.0.xsd';

        $this->assertInstanceOf(\DOMDocument::class, XmlUtils::loadFile($fixtures.'Book.xml', $resourceSchema));
        $this->assertInstanceOf(\DOMDocument::class, XmlUtils::loadFile($fixtures.'Book.services.xml', $servicesSchema));
        $this->assertInstanceOf(\DOMDocument::class, XmlUtils::loadFile($fixtures.'Tag.xml', $resourceSchema));
        $this->assertInstanceOf(\DOMDocument::class, XmlUtils::loadFile($fixtures.'Tag.services.xml', $servicesSchema));
        $this->assertInstanceOf(\DOMDocument::class, XmlUtils::loadFile($fixtures.'Dummy.xml', $resourceSchema));
    }
}
