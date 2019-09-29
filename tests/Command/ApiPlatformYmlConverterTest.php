<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Command;

use ConfigurationConverter\Converters\ConfigurationConverter;
use Symfony\Component\Filesystem\Filesystem;

class ApiPlatformYmlConverterTest extends AbstractConverterTest
{
    public function testCommandWithGoodConfigurationsArgument(): void
    {
        self::$commandTester->execute(
            [
                'command' => self::$command->getName(),
                '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
                '--configurations' => [ConfigurationConverter::CONVERT_API_PLATFORM],
                '--format' => 'yml',
                '-vvv' => '',
            ]
        );

        $apiPlatformOutput = self::$kernel->getProjectDir().self::API_PLATFORM_OUTPUT;
        $output = self::$kernel->getProjectDir().self::SERIALIZER_GROUP_OUTPUT;

        $filesystem = new Filesystem();
        $filesystem->remove($output);
        $filesystem->remove($apiPlatformOutput);

        $this->assertFileNotExists($apiPlatformOutput.'Book.yml');
        $this->assertFileNotExists($apiPlatformOutput.'Book.services.yml');
        $this->assertFileNotExists($output.'Book.yml');
        $this->assertFileNotExists($output.'Book.services.yml');

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] Converting resource:', $output);
    }

    public function testCommandWithoutOutputArgument(): void
    {
        self::$commandTester->execute(
            ['command' => self::$command->getName(), '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book', '--format' => 'yml']
        );

        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] Converting resource:', $output);
        $this->assertStringContainsString('ConfigurationConverter\Test\Fixtures\App\src\Entity\Book', $output);
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);

        self::$commandTester->execute(
            ['command' => self::$command->getName(), '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Tag', '--format' => 'yml']
        );
        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);

        self::$commandTester->execute(
            ['command' => self::$command->getName(), '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Dummy', '--format' => 'yml']
        );
        $output = self::$commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Check and paste this configuration:', $output);
    }

    public function testYmlNonSpecifiedResourcesOutput(): void
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
            '--format' => 'yml',
        ]);

        $this->assertFileNotExists($output.'Dummy.services.yml');
        $this->assertFileNotExists($output.'NotAResource.yml');

        $this->assertFileEquals($serializer_expected.'Book.yml', $serializer_output.'Book.yml');
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
