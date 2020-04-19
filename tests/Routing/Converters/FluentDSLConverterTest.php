<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Routing\Converters;

use ConfigurationConverter\Command\RouteFileConverterCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class FluentDSLConverterTest extends KernelTestCase
{
    protected static ?CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var RouteFileConverterCommand $cmd */
        $cmd = self::$container->get('configuration_converter.command.route_file');
        static::assertInstanceOf(RouteFileConverterCommand::class, $cmd);
        self::$commandTester = new CommandTester($cmd);
    }

    protected function tearDown(): void
    {
        self::$commandTester = null;
        parent::tearDown();
    }

    public function testFluent(): void
    {
        $fileName = 'empty_routing_file_to_convert';
        $outputFile = sprintf('%s/../../Fixtures/App/config/routes/%s.php', __DIR__, $fileName);
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }

        $tester = self::$commandTester;

        $tester->execute([
            'file' => sprintf('config/routes/%s.yaml', $fileName),
            '--output-format' => 'fluent',
        ]);

        $display = explode("\n", $tester->getDisplay(true));
        static::assertStringStartsWith(' Converting... ', $display[1]);
        static::assertStringStartsWith(' [OK] Written '.$fileName.'.php ', $display[3]);

        unlink($outputFile);
    }
}
