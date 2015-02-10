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
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Input\InputOption;
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
        $this->config
            ->addArgument('argument')
            ->addOption('option', 'o')
            ->beginCommand('command')->end()
        ;

        $application = new ConsoleApplication($this->config);

        $this->assertSame($this->config, $application->getConfig());

        $this->assertEquals(new InputDefinition(array(
            new InputArgument('argument'),
            new InputOption('option', 'o'),
        )), $application->getBaseInputDefinition());

        $this->assertEquals(new Command(
            $this->config->getCommandConfig('command'),
            $application->getBaseInputDefinition(),
            $application
        ), $application->getCommand('command'));
    }

    public function testCreateIgnoresDisabledCommands()
    {
        $this->config
            ->addArgument('argument')
            ->addOption('option', 'o')
            ->beginCommand('enabled')->enable()->end()
            ->beginCommand('disabled')->disable()->end()
        ;

        $application = new ConsoleApplication($this->config);

        $this->assertSame($this->config, $application->getConfig());

        $enabledCommand = new Command(
            $this->config->getCommandConfig('enabled'),
            $application->getBaseInputDefinition(),
            $application
        );

        $this->assertEquals(new CommandCollection(array($enabledCommand)), $application->getCommands());
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
