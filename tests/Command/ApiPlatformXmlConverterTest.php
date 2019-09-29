<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Command;

use ConfigurationConverter\Converters\ConfigurationConverter;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Filesystem\Filesystem;

class ApiPlatformXmlConverterTest extends AbstractConverterTest
{
    public function testCommandWithGoodConfigurationsArgument(): void
    {
        self::$commandTester->execute(
            [
                'command' => self::$command->getName(),
                '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
                '--configurations' => [ConfigurationConverter::CONVERT_API_PLATFORM],
            ]
        );

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] Converting resource:', $output);
    }

    public function testCommandWithBadConfigurationsArgument(): void
    {
        self::$commandTester->execute(
            [
                'command' => self::$command->getName(),
                '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
                '--configurations' => ['bad'],
            ]
        );

        $output = self::$commandTester->getDisplay();
        $this->assertStringNotContainsString('[NOTE] Converting resource:', $output);
    }

    public function testCommandWithoutOutputArgument(): void
    {
        self::$commandTester->execute(
            ['command' => self::$command->getName(), '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book']
        );

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] Converting resource:', $output);
        $this->assertStringContainsString('ConfigurationConverter\Test\Fixtures\App\src\Entity\Book', $output);
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);

        self::$commandTester->execute(
            ['command' => self::$command->getName(), '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Tag']
        );
        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);

        self::$commandTester->execute(
            ['command' => self::$command->getName(), '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Dummy']
        );
        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);
    }

    public function testCommandWithBadFormatArgument(): void
    {
        self::$commandTester->execute([
            'command' => self::$command->getName(),
            '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
            '--format' => 'badformat',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringNotContainsString('[OK] Check and paste this configuration', $output);
    }

    public function testCommandWithXmlFormatArgument(): void
    {
        self::$commandTester->execute([
            'command' => self::$command->getName(),
            '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
            '--format' => 'xml',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);
    }

    public function testCommandWithoutPermissionOutputArgument(): void
    {
        $filesystem = new Filesystem();
        $filesystem->chmod(self::$kernel->getProjectDir().'/../forbidenDir', 0555);
        $filesystem->chmod(self::$kernel->getProjectDir().'/../forbidenDir/Book.xml', 0444);

        self::$commandTester->execute([
            'command' => self::$command->getName(),
            '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
            '--api-platform-output' => self::$kernel->getProjectDir().'/../forbidenDir',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('Unable to write', $output);

        self::$commandTester->execute([
            'command' => self::$command->getName(),
            '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
            '--api-platform-output' => self::$kernel->getProjectDir().'/../forbidenDir/cannotcreatedir',
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[ERROR] Failed to create', $output);
    }

    public function testXmlNonSpecifiedResourcesOutput(): void
    {
        $serializer_output = self::$kernel->getProjectDir().self::SERIALIZER_GROUP_OUTPUT;
        $serializer_expected = self::$kernel->getProjectDir().self::SERIALIZER_GROUP_EXPECTED;
        $output = self::$kernel->getProjectDir().self::API_PLATFORM_OUTPUT;
        $expected = self::$kernel->getProjectDir().self::API_PLATFORM_EXPECTED;

        $filesystem = new Filesystem();
        $filesystem->remove($output);
        $filesystem->remove($serializer_output);

        self::$commandTester->execute([
            'command' => self::$command->getName(),
            '--api-platform-output' => $output,
            '--serializer-groups-output' => $serializer_output,
        ]);

        $resourceSchema = self::$kernel->getProjectDir().'/../../../vendor/api-platform/core/src/Metadata/schema/metadata.xsd';
        $servicesSchema = self::$kernel->getProjectDir().'/../../../vendor/symfony/dependency-injection/Loader/schema/dic/services/services-1.0.xsd';

        $this->assertInstanceOf(\DOMDocument::class, $book = XmlUtils::loadFile($output.'Book.xml', $resourceSchema));
        $this->assertInstanceOf(\DOMDocument::class, $bookServices = XmlUtils::loadFile($output.'Book.services.xml', $servicesSchema));
        $this->assertInstanceOf(\DOMDocument::class, $tag = XmlUtils::loadFile($output.'Tag.xml', $resourceSchema));
        $this->assertInstanceOf(\DOMDocument::class, $tagService = XmlUtils::loadFile($output.'Tag.services.xml', $servicesSchema));
        $this->assertInstanceOf(\DOMDocument::class, $dummy = XmlUtils::loadFile($output.'Dummy.xml', $resourceSchema));

        $this->assertFileNotExists($output.'Dummy.services.xml');
        $this->assertFileNotExists($output.'NotAResource.xml');

        $this->assertFileEquals($serializer_expected.'Book.xml', $serializer_output.'Book.xml');
        $this->assertFileEquals($expected.'Book.xml', $output.'Book.xml');
        $this->assertFileEquals($expected.'Book.services.xml', $output.'Book.services.xml');
        $this->assertFileEquals($expected.'Tag.xml', $output.'Tag.xml');
        $this->assertFileEquals($expected.'Tag.services.xml', $output.'Tag.services.xml');
        $this->assertFileEquals($expected.'Dummy.xml', $output.'Dummy.xml');
    }

    public function testXmlNonSpecifiedResourcesWithoutOutput(): void
    {
        self::$commandTester->execute([
            'command' => self::$command->getName(),
        ]);

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Book.xml', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Book.services.xml', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Dummy.xml', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Tag.xml', $output);
        $this->assertStringContainsString('# config/packages/api-platform/Tag.services.xml', $output);
    }
}
