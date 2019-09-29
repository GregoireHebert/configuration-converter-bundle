<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Command;

use ConfigurationConverter\Converters\ConfigurationConverter;
use Symfony\Component\Filesystem\Filesystem;

class SerializerGroupXmlConverterTest extends AbstractConverterTest
{
    public function testCommandWithGoodConfigurationsArgument(): void
    {
        self::$commandTester->execute(
            [
                'command' => self::$command->getName(),
                '--resource' => 'ConfigurationConverter\Test\Fixtures\App\src\Entity\Book',
                '--configurations' => [ConfigurationConverter::CONVERT_GROUPS],
                '--format' => 'xml',
                '-vvv' => '',
            ]
        );

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
                '--format' => 'xml',
                '--api-platform-output' => $apiPlatformOutput,
                '--serializer-groups-output' => $output,
                '-vvv' => '',
            ]
        );

        $this->assertFileNotExists($apiPlatformOutput.'Book.xml');
        $this->assertFileNotExists($apiPlatformOutput.'Book.services.xml');

        $this->assertFileExists($output.'Book.xml');
        $this->assertFileNotExists($output.'Book.services.xml');

        $this->assertFileEquals($expected.'Book.xml', $output.'Book.xml');
    }
}
