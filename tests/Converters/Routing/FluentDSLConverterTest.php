<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Converters\Routing;

use ConfigurationConverter\Command\RouteFileConverterCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class FluentDSLConverterTest extends KernelTestCase
{
    protected static Application $application;
    protected static Command $command;
    protected static CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        self::$command = self::$container->get('configuration_converter.command.route_file');
        self::$commandTester = new CommandTester(self::$command);

        static::assertInstanceOf(RouteFileConverterCommand::class, self::$command);
    }

    public function testSomething(): void
    {
        static::assertInstanceOf(RouteFileConverterCommand::class, self::$command);
    }
}
