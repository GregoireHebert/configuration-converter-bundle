<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Command;

use ConfigurationConverter\Command\ConverterCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractConverterTest extends KernelTestCase
{
    public const API_PLATFORM_OUTPUT = '/config/packages/api-platform/';
    public const API_PLATFORM_EXPECTED = '/config/packages/expected/';
    public const SERIALIZER_GROUP_OUTPUT = '/config/packages/serialization/';
    public const SERIALIZER_GROUP_EXPECTED = '/config/packages/expected_serialization/';

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
        self::$command = self::$application->find('configuration:convert');
        self::$commandTester = new CommandTester(self::$command);

        $this->assertInstanceOf(ConverterCommand::class, self::$command);
    }
}
