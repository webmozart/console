<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Command;

use PHPUnit_Framework_Assert;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NamedCommand;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\Config\SubCommandConfig;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Args\StringArgs;
use Webmozart\Console\Handler\CallableHandler;
use Webmozart\Console\Input\StringInput;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Application
     */
    private $application;

    protected function setUp()
    {
        $this->application = $this->getMock('Webmozart\Console\Api\Application\Application');
    }

    public function testCreate()
    {
        $config = new CommandConfig('command');
        $config->addArgument('argument');
        $config->addOption('option', 'o');

        $command = new Command($config, $this->application);

        $this->assertSame($config, $command->getConfig());
        $this->assertSame($this->application, $command->getApplication());

        $argsFormat = $command->getArgsFormat();

        $this->assertNull($argsFormat->getBaseFormat());
        $this->assertCount(1, $argsFormat->getArguments());
        $this->assertTrue($argsFormat->hasArgument('argument'));
        $this->assertCount(1, $argsFormat->getOptions());
        $this->assertTrue($argsFormat->hasOption('option'));
    }

    public function testInheritApplicationArgsFormat()
    {
        $baseFormat = ArgsFormat::build()
            ->addArgument(new Argument('global-argument'))
            ->addOption(new Option('global-option'))
            ->getFormat();

        $this->application->expects($this->any())
            ->method('getGlobalArgsFormat')
            ->willReturn($baseFormat);

        $config = new CommandConfig('command');
        $config->addArgument('argument');
        $config->addOption('option');

        $command = new Command($config, $this->application);
        $argsFormat = $command->getArgsFormat();

        $this->assertSame($baseFormat, $argsFormat->getBaseFormat());
        $this->assertCount(2, $argsFormat->getArguments());
        $this->assertTrue($argsFormat->hasArgument('argument'));
        $this->assertTrue($argsFormat->hasArgument('global-argument'));
        $this->assertCount(2, $argsFormat->getOptions());
        $this->assertTrue($argsFormat->hasOption('option'));
        $this->assertTrue($argsFormat->hasOption('global-option'));
    }

    public function testInheritParentArgsFormat()
    {
        $parentConfig = new CommandConfig('parent');
        $parentConfig->addArgument('parent-argument');
        $parentConfig->addOption('parent-option');

        $parentCommand = new Command($parentConfig, $this->application);

        $config = new CommandConfig('command');
        $config->addArgument('argument');
        $config->addOption('option');

        $command = new Command($config, $this->application, $parentCommand);
        $argsFormat = $command->getArgsFormat();

        $this->assertSame($parentCommand->getArgsFormat(), $argsFormat->getBaseFormat());
        $this->assertCount(2, $argsFormat->getArguments());
        $this->assertTrue($argsFormat->hasArgument('argument'));
        $this->assertTrue($argsFormat->hasArgument('parent-argument'));
        $this->assertCount(2, $argsFormat->getOptions());
        $this->assertTrue($argsFormat->hasOption('option'));
        $this->assertTrue($argsFormat->hasOption('parent-option'));
    }

    public function testGetParentCommand()
    {
        $parentCommand = new Command(new CommandConfig('parent'));
        $command = new Command(new CommandConfig('command'), null, $parentCommand);

        $this->assertSame($parentCommand, $command->getParentCommand());
    }

    public function testGetSubCommands()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig($subConfig1 = new SubCommandConfig('sub1'));
        $config->addSubCommandConfig($subConfig2 = new SubCommandConfig('sub2'));
        $command = new Command($config, $this->application);

        $this->assertEquals(new CommandCollection(array(
            'sub1' => new NamedCommand($subConfig1, $this->application, $command),
            'sub2' => new NamedCommand($subConfig2, $this->application, $command),
        )), $command->getSubCommands());
    }

    public function testGetSubCommand()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig($subConfig = new SubCommandConfig('sub'));
        $command = new Command($config, $this->application);

        $subCommand = new NamedCommand($subConfig, $this->application, $command);

        $this->assertEquals($subCommand, $command->getSubCommand('sub'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\NoSuchCommandException
     * @expectedExceptionMessage foobar
     */
    public function testGetSubCommandFailsIfNotFound()
    {
        $command = new Command(new CommandConfig('command'));

        $command->getSubCommand('foobar');
    }

    public function testHasSubCommand()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig('sub'));
        $command = new Command($config);

        $this->assertTrue($command->hasSubCommand('sub'));
        $this->assertFalse($command->hasSubCommand('foobar'));
    }

    public function testHasSubCommands()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig('sub'));
        $command = new Command($config);

        $this->assertTrue($command->hasSubCommands());
    }

    public function testHasNoSubCommands()
    {
        $command = new Command(new CommandConfig('command'));

        $this->assertFalse($command->hasSubCommands());
    }

    public function testGetOptionCommands()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig($optionConfig1 = new OptionCommandConfig('option1', 'a'));
        $config->addOptionCommandConfig($optionConfig2 = new OptionCommandConfig('option2', 'b'));
        $command = new Command($config, $this->application);

        $this->assertEquals(new CommandCollection(array(
            'option1' => new NamedCommand($optionConfig1, $this->application, $command),
            'option2' => new NamedCommand($optionConfig2, $this->application, $command),
        )), $command->getOptionCommands());
    }

    public function testGetOptionCommandByLongName()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig($optionConfig = new OptionCommandConfig('option', 'o'));
        $command = new Command($config, $this->application);

        $optionCommand = new NamedCommand($optionConfig, $this->application, $command);

        $this->assertEquals($optionCommand, $command->getOptionCommand('option'));
    }

    public function testGetOptionCommandByShortName()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig($optionConfig = new OptionCommandConfig('option', 'o'));
        $command = new Command($config, $this->application);

        $optionCommand = new NamedCommand($optionConfig, $this->application, $command);

        $this->assertEquals($optionCommand, $command->getOptionCommand('o'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\NoSuchCommandException
     * @expectedExceptionMessage foobar
     */
    public function testGetOptionCommandFailsIfNotFound()
    {
        $command = new Command(new CommandConfig('command'));

        $command->getOptionCommand('foobar');
    }

    public function testHasOptionCommand()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig(new OptionCommandConfig('option', 'o'));
        $command = new Command($config);

        $this->assertTrue($command->hasOptionCommand('option'));
        $this->assertTrue($command->hasOptionCommand('o'));
        $this->assertFalse($command->hasOptionCommand('foobar'));
    }

    public function testHasOptionCommands()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig(new OptionCommandConfig('option'));
        $command = new Command($config);

        $this->assertTrue($command->hasOptionCommands());
    }

    public function testHasNoOptionCommands()
    {
        $command = new Command(new CommandConfig('command'));

        $this->assertFalse($command->hasOptionCommands());
    }

    public function testGetUnnamedCommands()
    {
        $config = new CommandConfig('command');
        $config->addUnnamedCommandConfig($subConfig1 = new SubCommandConfig());
        $config->addUnnamedCommandConfig($subConfig2 = new SubCommandConfig());
        $command = new Command($config, $this->application);

        $this->assertEquals(array(
            new Command($subConfig1, $this->application, $command),
            new Command($subConfig2, $this->application, $command),
        ), $command->getUnnamedCommands());
    }

    public function testHasUnnamedCommands()
    {
        $config = new CommandConfig('command');
        $config->addUnnamedCommandConfig(new SubCommandConfig());
        $command = new Command($config);

        $this->assertTrue($command->hasUnnamedCommands());
    }

    public function testHasNoUnnamedCommands()
    {
        $command = new Command(new CommandConfig('command'));

        $this->assertFalse($command->hasUnnamedCommands());
    }

    public function testGetDefaultCommands()
    {
        $config = new CommandConfig('command');
        $config->addUnnamedCommandConfig($subConfig1 = new SubCommandConfig());
        $config->addSubCommandConfig($subConfig2 = new SubCommandConfig('sub1'));
        $config->addSubCommandConfig($subConfig3 = new SubCommandConfig('sub2'));
        $config->addOptionCommandConfig($optionConfig1 = new OptionCommandConfig('option1'));
        $config->addOptionCommandConfig($optionConfig2 = new OptionCommandConfig('option2'));
        $config->addDefaultCommand('sub2');
        $config->addDefaultCommand('option1');

        $command = new Command($config, $this->application);

        $this->assertEquals(array(
            new Command($subConfig1, $this->application, $command),
            new NamedCommand($subConfig3, $this->application, $command),
            new NamedCommand($optionConfig1, $this->application, $command),
        ), $command->getDefaultCommands());
    }

    public function testHasDefaultCommandsIfUnnamed()
    {
        $config = new CommandConfig('command');
        $config->addUnnamedCommandConfig(new SubCommandConfig());
        $command = new Command($config);

        $this->assertTrue($command->hasDefaultCommands());
    }

    public function testHasDefaultCommandsIfDefaultCommand()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig('sub'));
        $config->addDefaultCommand('sub');
        $command = new Command($config);

        $this->assertTrue($command->hasDefaultCommands());
    }

    public function testHasNoDefaultCommands()
    {
        $command = new Command(new CommandConfig('command'));

        $this->assertFalse($command->hasDefaultCommands());
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailsIfSubCommandSameNameAsOtherSubCommand()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig('sub'));
        $config->addSubCommandConfig(new SubCommandConfig('sub'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailsIfSubCommandSameNameAsOptionCommandLong()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig(new OptionCommandConfig('option', 'o'));
        $config->addSubCommandConfig(new SubCommandConfig('option'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailsIfSubCommandSameNameAsOptionCommandShort()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig(new OptionCommandConfig('option', 'o'));
        $config->addSubCommandConfig(new SubCommandConfig('o'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailsIfOptionCommandSameNameAsOptionCommandLong()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig(new OptionCommandConfig('option', 'o'));
        $config->addOptionCommandConfig(new OptionCommandConfig('option'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailsIfOptionCommandSameNameAsOptionCommandShort()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig(new OptionCommandConfig('option1', 'o'));
        $config->addOptionCommandConfig(new OptionCommandConfig('option2', 'o'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailsIfOptionCommandSameNameAsOptionLong()
    {
        $config = new CommandConfig('command');
        $config->addOption('option');
        $config->addOptionCommandConfig(new OptionCommandConfig('option'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailsIfOptionCommandSameNameAsOptionShort()
    {
        $config = new CommandConfig('command');
        $config->addOption('option1', 'o');
        $config->addOptionCommandConfig(new OptionCommandConfig('option2', 'o'));

        new Command($config);
    }

    public function testParseArgs()
    {
        $rawArgs = $this->getMock('Webmozart\Console\Api\Args\RawArgs');
        $parsedArgs = new Args(new ArgsFormat());
        $parser = $this->getMock('Webmozart\Console\Api\Args\ArgsParser');
        $config = new CommandConfig('command');
        $config->setArgsParser($parser);
        $command = new Command($config);

        $parser->expects($this->once())
            ->method('parseArgs')
            ->with($this->identicalTo($rawArgs))
            ->willReturn($parsedArgs);

        $this->assertSame($parsedArgs, $command->parseArgs($rawArgs));
    }

    public function testHandle()
    {
        $args = new Args(new ArgsFormat());
        $input = $this->getMock('Webmozart\Console\Api\Input\Input');
        $output = $this->getMock('Webmozart\Console\Api\Output\Output');
        $errorOutput = $this->getMock('Webmozart\Console\Api\Output\Output');
        $handler = $this->getMock('Webmozart\Console\Api\Handler\CommandHandler');

        $config = new CommandConfig('command');
        $config->setHandler($handler);
        $command = new Command($config);

        $handler->expects($this->at(0))
            ->method('initialize')
            ->with($command, $output, $errorOutput);

        $handler->expects($this->at(1))
            ->method('handle')
            ->with($args, $input)
            ->willReturn(123);

        $this->assertSame(123, $command->handle($args, $input, $output, $errorOutput));
    }

    public function testRun()
    {
        $rawArgs = $this->getMock('Webmozart\Console\Api\Args\RawArgs');
        $parsedArgs = new Args(new ArgsFormat());
        $input = $this->getMock('Webmozart\Console\Api\Input\Input');
        $output = $this->getMock('Webmozart\Console\Api\Output\Output');
        $errorOutput = $this->getMock('Webmozart\Console\Api\Output\Output');
        $parser = $this->getMock('Webmozart\Console\Api\Args\ArgsParser');
        $handler = $this->getMock('Webmozart\Console\Api\Handler\CommandHandler');

        $config = new CommandConfig('command');
        $config->setArgsParser($parser);
        $config->setHandler($handler);
        $command = new Command($config);

        $parser->expects($this->once())
            ->method('parseArgs')
            ->with($this->identicalTo($rawArgs))
            ->willReturn($parsedArgs);

        $handler->expects($this->at(0))
            ->method('initialize')
            ->with($command, $output, $errorOutput);

        $handler->expects($this->at(1))
            ->method('handle')
            ->with($parsedArgs, $input)
            ->willReturn(123);

        $this->assertSame(123, $command->run($rawArgs, $input, $output, $errorOutput));
    }
}
