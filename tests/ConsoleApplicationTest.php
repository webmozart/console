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
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NamedCommand;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Args\StringArgs;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Handler\CallbackHandler;
use Webmozart\Console\IO\Input\BufferedInput;
use Webmozart\Console\IO\Output\BufferedOutput;

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

    public function testResolveCommand()
    {
        $args = new StringArgs('');
        $resolver = $this->getMock('Webmozart\Console\Api\Resolver\CommandResolver');
        $this->config->setCommandResolver($resolver);

        $application = new ConsoleApplication($this->config);

        $resolver->expects($this->once())
            ->method('resolveCommand')
            ->with($args, $application)
            ->willReturn('RESOLVED COMMAND');

        $this->assertSame('RESOLVED COMMAND', $application->resolveCommand($args));
    }

    /**
     * @dataProvider getRunConfigurations
     */
    public function testRunCommand($argString, $configCallback)
    {
        $callback = function (Command $command, Args $args, IO $io) {
            $io->write($io->readLine());
            $io->error($io->readLine());

            return 123;
        };

        $configCallback($this->config, $callback);

        $args = new StringArgs($argString);
        $input = new BufferedInput("line1\nline2");
        $output = $buffer1 = new BufferedOutput();
        $errorOutput = $buffer2 = new BufferedOutput();
        $application = new ConsoleApplication($this->config);

        $this->assertSame(123, $application->run($args, $input, $output, $errorOutput));
        $this->assertSame("line1\n", $buffer1->fetch());
        $this->assertSame("line2", $buffer2->fetch());
    }

    public function getRunConfigurations()
    {
        return array(
            // Simple command
            array(
                'list',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginCommand('list')
                            ->setHandler(new CallbackHandler($callback))
                        ->end()
                    ;
                }
            ),
            // Default command
            array(
                '',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->addDefaultCommand('list')
                        ->beginCommand('list')
                            ->setHandler(new CallbackHandler($callback))
                        ->end()
                    ;
                }
            ),
            // Unnamed command
            array(
                '',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginUnnamedCommand()
                            ->setHandler(new CallbackHandler($callback))
                        ->end()
                    ;
                }
            ),
            // Sub-command
            array(
                'server add',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginCommand('server')
                            ->beginSubCommand('add')
                                ->setHandler(new CallbackHandler($callback))
                            ->end()
                        ->end()
                    ;
                }
            ),
            // Default sub-command
            array(
                'server',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginCommand('server')
                            ->addDefaultCommand('add')
                            ->beginSubCommand('add')
                                ->setHandler(new CallbackHandler($callback))
                            ->end()
                        ->end()
                    ;
                }
            ),
            // Option command
            array(
                'server --add',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginCommand('server')
                            ->beginOptionCommand('add')
                                ->setHandler(new CallbackHandler($callback))
                            ->end()
                        ->end()
                    ;
                }
            ),
            // Default option command
            array(
                'server',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginCommand('server')
                            ->addDefaultCommand('add')
                            ->beginOptionCommand('add')
                                ->setHandler(new CallbackHandler($callback))
                            ->end()
                        ->end()
                    ;
                }
            ),
            // Unnamed sub-command
            array(
                'server',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginCommand('server')
                            ->beginUnnamedCommand()
                                ->setHandler(new CallbackHandler($callback))
                            ->end()
                        ->end()
                    ;
                }
            ),
        );
    }
}
