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

use PHPUnit_Framework_Assert;
use PHPUnit_Framework_TestCase;
use stdClass;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NoSuchCommandException;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Event\ConfigEvent;
use Webmozart\Console\Api\Event\ConsoleEvents;
use Webmozart\Console\Api\Event\PreResolveEvent;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Args\StringArgs;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Handler\CallbackHandler;
use Webmozart\Console\IO\Input\BufferedInput;
use Webmozart\Console\IO\Output\BufferedOutput;
use Webmozart\Console\IO\RawIO;

/**
 * @since  1.0
 *
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
        $this->config->setCatchExceptions(false);
        $this->config->setTerminateAfterRun(false);
        $this->config->setIOFactory(function ($application, $args, $input, $output, $errorOutput) {
            return new RawIO($input, $output, $errorOutput);
        });
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

    public function testCreateWithConfigClosure()
    {
        $config = $this->config;
        $config->addArgument('argument');
        $config->addOption('option', 'o');

        // This feature is useful when the Config constructor might throw an
        // exception which should be rendered nicely
        $application = new ConsoleApplication(function () use ($config) {
            return $config;
        });

        $this->assertSame($this->config, $application->getConfig());

        $this->assertEquals(new ArgsFormat(array(
            new Argument('argument'),
            new Option('option', 'o'),
        )), $application->getGlobalArgsFormat());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfConfigNeitherCallableNorConfigClass()
    {
        new ConsoleApplication(new stdClass());
    }

    public function testGetCommands()
    {
        $this->config->addCommandConfig($config1 = new CommandConfig('command1'));
        $this->config->addCommandConfig($config2 = new CommandConfig('command2'));

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(new CommandCollection(array(
            new Command($config1, $application),
            new Command($config2, $application),
        )), $application->getCommands());
    }

    public function testGetCommandsExcludesDisabledCommands()
    {
        $this->config->addCommandConfig($enabled = CommandConfig::create('command1')->enable());
        $this->config->addCommandConfig($disabled = CommandConfig::create('command2')->disable());

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(new CommandCollection(array(
            new Command($enabled, $application),
        )), $application->getCommands());
    }

    public function testGetCommand()
    {
        $this->config->addCommandConfig($config = new CommandConfig('command'));

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(new Command($config, $application), $application->getCommand('command'));
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

    public function testGetNamedCommands()
    {
        $this->config->addCommandConfig($config1 = new CommandConfig('command1'));
        $this->config->addCommandConfig($config2 = new CommandConfig('command2'));
        $this->config->addCommandConfig($config3 = new CommandConfig('command3'));

        $config2->markAnonymous();
        $config3->markDefault();

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(new CommandCollection(array(
            new Command($config1, $application),
            new Command($config3, $application),
        )), $application->getNamedCommands());
    }

    public function testHasNamedCommands()
    {
        $this->config->addCommandConfig(new CommandConfig('command'));

        $application = new ConsoleApplication($this->config);

        $this->assertTrue($application->hasNamedCommands());
    }

    public function testHasNoNamedCommands()
    {
        $config = new CommandConfig('command');
        $config->markAnonymous();

        $this->config->addCommandConfig($config);

        $application = new ConsoleApplication($this->config);

        $this->assertFalse($application->hasNamedCommands());
    }

    public function testGetDefaultCommands()
    {
        $this->config->addCommandConfig($config1 = new CommandConfig('command1'));
        $this->config->addCommandConfig($config2 = new CommandConfig('command2'));
        $this->config->addCommandConfig($config3 = new CommandConfig('command3'));

        $config1->markDefault();
        $config3->markDefault();

        $application = new ConsoleApplication($this->config);

        $this->assertEquals(new CommandCollection(array(
            new Command($config1, $application),
            new Command($config3, $application),
        )), $application->getDefaultCommands());
    }

    public function testHasDefaultCommands()
    {
        $config = new CommandConfig('command');
        $config->markDefault();

        $this->config->addCommandConfig($config);

        $application = new ConsoleApplication($this->config);

        $this->assertTrue($application->hasDefaultCommands());
    }

    public function testHasNoDefaultCommands()
    {
        $this->config->addCommandConfig(new CommandConfig('command'));

        $application = new ConsoleApplication($this->config);

        $this->assertFalse($application->hasDefaultCommands());
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfNoCommandName()
    {
        $this->config->addCommandConfig(new CommandConfig());

        new ConsoleApplication($this->config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfDuplicateCommandName()
    {
        $this->config->addCommandConfig(new CommandConfig('command'));
        $this->config->addCommandConfig(new CommandConfig('command'));

        new ConsoleApplication($this->config);
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

    public function testResolveCommandDispatchesEvent()
    {
        $args = new StringArgs('');
        $resolver = $this->getMock('Webmozart\Console\Api\Resolver\CommandResolver');
        $resolvedCommand = $this->getMockBuilder('Webmozart\Console\Api\Resolver\ResolvedCommand')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->setCommandResolver($resolver);

        $this->config->addEventListener(ConsoleEvents::PRE_RESOLVE, function (PreResolveEvent $event) use ($resolvedCommand) {
            $event->setResolvedCommand($resolvedCommand);
        });

        $application = new ConsoleApplication($this->config);

        $resolver->expects($this->never())
            ->method('resolveCommand');

        $this->assertSame($resolvedCommand, $application->resolveCommand($args));
    }

    /**
     * @dataProvider getRunConfigurations
     */
    public function testRunCommand($argString, $configCallback)
    {
        $callback = function (Args $args, IO $io) {
            $io->write($io->readLine());
            $io->error($io->readLine());

            return 123;
        };

        $configCallback($this->config, $callback);

        $args = new StringArgs($argString);
        $input = new BufferedInput("line1\nline2");
        $output = new BufferedOutput();
        $errorOutput = new BufferedOutput();
        $application = new ConsoleApplication($this->config);

        $this->assertSame(123, $application->run($args, $input, $output, $errorOutput));
        $this->assertSame("line1\n", $output->fetch());
        $this->assertSame('line2', $errorOutput->fetch());
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
                },
            ),
            // Default command
            array(
                '',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginCommand('list')
                            ->markDefault()
                            ->setHandler(new CallbackHandler($callback))
                        ->end()
                    ;
                },
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
                },
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
                },
            ),
            // Default sub-command
            array(
                'server',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginCommand('server')
                            ->beginSubCommand('add')
                                ->markDefault()
                                ->setHandler(new CallbackHandler($callback))
                            ->end()
                        ->end()
                    ;
                },
            ),
            // Default option command
            array(
                'server',
                function (ApplicationConfig $config, $callback) {
                    $config
                        ->beginCommand('server')
                            ->beginOptionCommand('add')
                                ->markDefault()
                                ->setHandler(new CallbackHandler($callback))
                            ->end()
                        ->end()
                    ;
                },
            ),
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfNoIOFactory()
    {
        $this->config->setIOFactory(null);

        $args = new StringArgs('');
        $application = new ConsoleApplication($this->config);

        $application->run($args);
    }

    public function testPrintExceptionIfCatchingActive()
    {
        $this->config
            ->setCatchExceptions(true)
            ->beginCommand('list')
                ->setHandler(new CallbackHandler(function () {
                    throw NoSuchCommandException::forCommandName('foobar', 123);
                }))
            ->end()
        ;

        $args = new StringArgs('list');
        $input = new BufferedInput();
        $output = new BufferedOutput();
        $errorOutput = new BufferedOutput();
        $application = new ConsoleApplication($this->config);

        $this->assertSame(123, $application->run($args, $input, $output, $errorOutput));
        $this->assertSame('', $output->fetch());
        $this->assertSame("fatal: The command \"foobar\" does not exist.\n", $errorOutput->fetch());
    }

    public function testNormalizeNegativeExceptionCodeToOne()
    {
        $this->config
            ->setCatchExceptions(true)
            ->beginCommand('list')
                ->setHandler(new CallbackHandler(function () {
                    throw NoSuchCommandException::forCommandName('foobar', -1);
                }))
            ->end()
        ;

        $args = new StringArgs('list');
        $input = new BufferedInput();
        $output = new BufferedOutput();
        $errorOutput = new BufferedOutput();
        $application = new ConsoleApplication($this->config);

        $this->assertSame(1, $application->run($args, $input, $output, $errorOutput));
    }

    public function testNormalizeLargeExceptionCodeTo255()
    {
        $this->config
            ->setCatchExceptions(true)
            ->beginCommand('list')
                ->setHandler(new CallbackHandler(function () {
                    throw NoSuchCommandException::forCommandName('foobar', 256);
                }))
            ->end()
        ;

        $args = new StringArgs('list');
        $input = new BufferedInput();
        $output = new BufferedOutput();
        $errorOutput = new BufferedOutput();
        $application = new ConsoleApplication($this->config);

        // 255 is the highest supported exit status of a process
        $this->assertSame(255, $application->run($args, $input, $output, $errorOutput));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\NoSuchCommandException
     */
    public function testThrowExceptionIfCatchingNotActive()
    {
        $this->config
            ->setCatchExceptions(false)
            ->beginCommand('list')
                ->setHandler(new CallbackHandler(function () {
                    throw NoSuchCommandException::forCommandName('foobar', 123);
                }))
            ->end()
        ;

        $args = new StringArgs('list');
        $input = new BufferedInput();
        $output = new BufferedOutput();
        $errorOutput = new BufferedOutput();
        $application = new ConsoleApplication($this->config);

        $application->run($args, $input, $output, $errorOutput);
    }

    public function testTerminateAfterRun()
    {
        exec('/usr/bin/env php '.__DIR__.'/Fixtures/terminate-after-run.php', $output, $status);

        echo implode("\n", $output);

        $this->assertSame(123, $status);
    }

    public function testDispatchConfigEvent()
    {
        $config = $this->config;
        $dispatched = false;

        $this->config->addEventListener(ConsoleEvents::CONFIG, function (ConfigEvent $event) use ($config, &$dispatched) {
            PHPUnit_Framework_Assert::assertSame($config, $event->getConfig());
            $dispatched = true;
        });

        $this->assertFalse($dispatched);

        new ConsoleApplication($this->config);

        $this->assertTrue($dispatched);
    }
}
