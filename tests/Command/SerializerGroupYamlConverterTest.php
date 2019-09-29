<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Command;

use ConfigurationConverter\Converters\ConfigurationConverter;
use Symfony\Component\Filesystem\Filesystem;

class SerializerGroupYamlConverterTest extends AbstractConverterTest
{
    public function testCommandWithGoodConfigurationsArgument(): void
    {
        self::$commandTester->execute(
            [
                'command' => self::$command->getName(),
                '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
                '--configurations' => [ConfigurationConverter::CONVERT_GROUPS],
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

    public function testCommandWithOutputArgument(): void
    {
        $apiPlatformOutput = self::$kernel->getProjectDir().self::API_PLATFORM_OUTPUT;
        $output = self::$kernel->getProjectDir().self::SERIALIZER_GROUP_OUTPUT;
        $expected = self::$kernel->getProjectDir().self::SERIALIZER_GROUP_EXPECTED;

        $filesystem = new Filesystem();
        $filesystem->remove($output);
        $filesystem->remove($apiPlatformOutput);

        self::$commandTester->execute(
            [
                'command' => self::$command->getName(),
                '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
                '--configurations' => [ConfigurationConverter::CONVERT_GROUPS],
                '--format' => 'yml',
                '--api-platform-output' => $apiPlatformOutput,
                '--serializer-groups-output' => $output,
                '-vvv' => '',
            ]
        );

        $this->assertFileNotExists($apiPlatformOutput.'Book.yml');
        $this->assertFileNotExists($apiPlatformOutput.'Book.services.yml');

        $this->assertFileExists($output.'Book.yml');
        $this->assertFileNotExists($output.'Book.services.yml');
        $this->assertFileEquals($expected.'Book.yml', $output.'Book.yml');
    }
}
