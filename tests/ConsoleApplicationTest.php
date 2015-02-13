<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Console\Adapter\InputInterfaceAdapter;
use Webmozart\Console\Adapter\OutputInterfaceAdapter;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NamedCommand;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\ConsoleApplication;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ConsoleApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ApplicationConfig
     */
    private $config;

    protected function setUp()
    {
        $this->config = new ApplicationConfig();
        $this->config->setTerminateAfterRun(false);
        $this->config->setCatchExceptions(false);
    }

    public function testCreate()
    {
        $this->config->addArgument('argument');
        $this->config->addOption('option', 'o');

        $application = new ConsoleApplication($this->config);

        $this->assertSame($this->config, $application->getConfig());

        $this->assertEquals(new ArgsFormat(array(
            new Argument('argument'),
            new Option('option', 'o'),
        )), $application->getGlobalArgsFormat());
    }

    public function testGetCommands()
    {
        $this->config->addCommandConfig($config1 = new CommandConfig('command1'));
        $this->config->addCommandConfig($config2 = new CommandConfig('command2'));

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(new CommandCollection(array(
            new NamedCommand($config1, $application),
            new NamedCommand($config2, $application),
        )), $application->getCommands());
    }

    public function testGetCommandsExcludesDisabledCommands()
    {
        $this->config->addCommandConfig($enabled = CommandConfig::create('command1')->enable());
        $this->config->addCommandConfig($disabled = CommandConfig::create('command2')->disable());

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(new CommandCollection(array(
            new NamedCommand($enabled, $application),
        )), $application->getCommands());
    }

    public function testGetCommand()
    {
        $this->config->addCommandConfig($config = new CommandConfig('command'));

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(new NamedCommand($config, $application), $application->getCommand('command'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\NoSuchCommandException
     * @expectedExceptionMessage foobar
     */
    public function testGetCommandFailsIfNotFound()
    {
        $application = new ConsoleApplication($this->config);

        $application->getCommand('foobar');
    }

    public function testHasCommand()
    {
        $this->config->addCommandConfig($config = new CommandConfig('command'));

        $application = new ConsoleApplication($this->config);

        $this->assertTrue($application->hasCommand('command'));
        $this->assertFalse($application->hasCommand('foobar'));
    }

    public function testHasCommands()
    {
        $this->config->addCommandConfig($config = new CommandConfig('command'));

        $application = new ConsoleApplication($this->config);

        $this->assertTrue($application->hasCommands());
    }

    public function testHasNoCommands()
    {
        $application = new ConsoleApplication($this->config);

        $this->assertFalse($application->hasCommands());
    }

    public function testGetUnnamedCommands()
    {
        $this->config->addUnnamedCommandConfig($config1 = CommandConfig::create()->setProcessTitle('title1'));
        $this->config->addUnnamedCommandConfig($config2 = CommandConfig::create()->setProcessTitle('title2'));

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(array(
            new Command($config1, $application),
            new Command($config2, $application),
        ), $application->getUnnamedCommands());
    }

    public function testHasUnnamedCommands()
    {
        $this->config->addUnnamedCommandConfig(new CommandConfig());

        $application = new ConsoleApplication($this->config);

        $this->assertTrue($application->hasUnnamedCommands());
    }

    public function testHasNoUnnamedCommands()
    {
        $application = new ConsoleApplication($this->config);

        $this->assertFalse($application->hasUnnamedCommands());
    }

    public function testGetDefaultCommands()
    {
        $this->config->addUnnamedCommandConfig($config1 = CommandConfig::create()->setProcessTitle('title'));
        $this->config->addCommandConfig($config2 = new CommandConfig('command1'));
        $this->config->addCommandConfig($config3 = new CommandConfig('command2'));
        $this->config->addDefaultCommand('command2');

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(array(
            new Command($config1, $application),
            new NamedCommand($config3, $application),
        ), $application->getDefaultCommands());
    }

    public function testHasDefaultCommandsIfUnnamedCommands()
    {
        $this->config->addUnnamedCommandConfig(new CommandConfig());

        $application = new ConsoleApplication($this->config);

        $this->assertTrue($application->hasDefaultCommands());
    }

    public function testHasDefaultCommandsIfDefaultCommands()
    {
        $this->config->addCommandConfig(new CommandConfig('command'));
        $this->config->addDefaultCommand('command');

        $application = new ConsoleApplication($this->config);

        $this->assertTrue($application->hasDefaultCommands());
    }

    public function testHasNoDefaultCommands()
    {
        $application = new ConsoleApplication($this->config);

        $this->assertFalse($application->hasDefaultCommands());
    }

    public function testRunCommand()
    {
        $callback = function (Input $input, Output $output, Output $errorOutput) {
            $output->writeln('stdout: '.$input->toString());
            $errorOutput->writeln('stderr: '.$input->toString());

            return 123;
        };

        $this->config
            ->beginCommand('list')
                ->setCallback($callback)
            ->end()
        ;

        $input = new InputInterfaceAdapter(new StringInput('list'));
        $output = new OutputInterfaceAdapter($buffer = new BufferedOutput());
        $runner = new ConsoleApplication($this->config);

        $this->assertSame(123, $runner->run($input, $output));
        $this->assertSame("stdout: list\nstderr: list\n", $buffer->fetch());
    }

    public function testRunDefaultCommand()
    {
        $callback = function (Input $input, Output $output, Output $errorOutput) {
            $output->writeln('stdout: '.$input->toString());
            $errorOutput->writeln('stderr: '.$input->toString());

            return 123;
        };

        $this->config
            ->beginCommand('list')
                ->setCallback($callback)
            ->end()
            ->setDefaultCommand('list')
        ;

        $input = new InputInterfaceAdapter(new StringInput(''));
        $output = new OutputInterfaceAdapter($buffer = new BufferedOutput());
        $runner = new ConsoleApplication($this->config);

        $this->assertSame(123, $runner->run($input, $output));
        $this->assertSame("stdout: \nstderr: \n", $buffer->fetch());
    }

    public function testRunDefaultSubCommand()
    {
        $callback = function (Input $input, Output $output, Output $errorOutput) {
            $output->writeln('stdout: '.$input->toString());
            $errorOutput->writeln('stderr: '.$input->toString());

            return 123;
        };

        $this->config
            ->beginCommand('server')
                ->beginSubCommand('list')
                    ->setCallback($callback)
                ->end()
                ->setDefaultSubCommand('list')
            ->end()
        ;

        $input = new InputInterfaceAdapter(new StringInput('server'));
        $output = new OutputInterfaceAdapter($buffer = new BufferedOutput());
        $runner = new ConsoleApplication($this->config);

        $this->assertSame(123, $runner->run($input, $output));
        $this->assertSame("stdout: server\nstderr: server\n", $buffer->fetch());
    }

    public function testRunDefaultOptionCommand()
    {
        $callback = function (Input $input, Output $output, Output $errorOutput) {
            $output->writeln('stdout: '.$input->toString());
            $errorOutput->writeln('stderr: '.$input->toString());

            return 123;
        };

        $this->config
            ->beginCommand('server')
                ->beginOptionCommand('list')
                    ->setCallback($callback)
                ->end()
                ->setDefaultOptionCommand('list')
            ->end()
        ;

        $input = new InputInterfaceAdapter(new StringInput('server'));
        $output = new OutputInterfaceAdapter($buffer = new BufferedOutput());
        $runner = new ConsoleApplication($this->config);

        $this->assertSame(123, $runner->run($input, $output));
        $this->assertSame("stdout: server\nstderr: server\n", $buffer->fetch());
    }
}
