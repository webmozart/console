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

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\Config\SubCommandConfig;
use Webmozart\Console\Api\Event\ConsoleEvents;
use Webmozart\Console\Api\Event\PreHandleEvent;

/**
 * @since  1.0
 *
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
        $this->application->expects($this->any())
            ->method('getConfig')
            ->willReturn(new ApplicationConfig());
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

    /**
     * @expectedException \LogicException
     */
    public function testCreateFailsIfNoName()
    {
        new Command(new CommandConfig());
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
        $config->addSubCommandConfig($subConfig2 = new OptionCommandConfig('sub2'));
        $command = new Command($config, $this->application);

        $this->assertEquals(new CommandCollection(array(
            'sub1' => new Command($subConfig1, $this->application, $command),
            'sub2' => new Command($subConfig2, $this->application, $command),
        )), $command->getSubCommands());
    }

    public function testIgnoreDisabledSubCommands()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig($subConfig1 = new SubCommandConfig('sub1'));
        $config->addSubCommandConfig($subConfig2 = new OptionCommandConfig('sub2'));

        $subConfig1->enable();
        $subConfig2->disable();

        $command = new Command($config, $this->application);

        $this->assertEquals(new CommandCollection(array(
            'sub1' => new Command($subConfig1, $this->application, $command),
        )), $command->getSubCommands());
    }

    public function testGetSubCommand()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig($subConfig = new SubCommandConfig('sub'));
        $command = new Command($config, $this->application);

        $subCommand = new Command($subConfig, $this->application, $command);

        $this->assertEquals($subCommand, $command->getSubCommand('sub'));
    }

    public function testGetSubCommandByShortName()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig($subConfig = new OptionCommandConfig('sub', 's'));
        $command = new Command($config, $this->application);

        $subCommand = new Command($subConfig, $this->application, $command);

        $this->assertEquals($subCommand, $command->getSubCommand('s'));
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

    public function testGetNamedSubCommands()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig($subConfig1 = new SubCommandConfig('sub1'));
        $config->addSubCommandConfig($subConfig2 = new SubCommandConfig('sub2'));
        $config->addSubCommandConfig($subConfig3 = new SubCommandConfig('sub3'));

        $subConfig2->markAnonymous();
        $subConfig3->markDefault();

        $command = new Command($config);

        $this->assertEquals(new CommandCollection(array(
            new Command($subConfig1, null, $command),
            new Command($subConfig3, null, $command),
        )), $command->getNamedSubCommands());
    }

    public function testHasNamedSubCommands()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig('sub'));

        $command = new Command($config);

        $this->assertTrue($command->hasNamedSubCommands());
    }

    public function testHasNoNamedSubCommands()
    {
        $config = new CommandConfig('command');
        $subConfig = new SubCommandConfig('sub');
        $subConfig->markAnonymous();

        $config->addSubCommandConfig($subConfig);

        $command = new Command($config);

        $this->assertFalse($command->hasNamedSubCommands());
    }

    public function testGetDefaultSubCommands()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig($subConfig1 = new SubCommandConfig('sub1'));
        $config->addSubCommandConfig($subConfig2 = new SubCommandConfig('sub2'));
        $config->addSubCommandConfig($subConfig3 = new SubCommandConfig('sub3'));

        $subConfig1->markDefault();
        $subConfig3->markDefault();

        $command = new Command($config);

        $this->assertEquals(new CommandCollection(array(
            new Command($subConfig1, null, $command),
            new Command($subConfig3, null, $command),
        )), $command->getDefaultSubCommands());
    }

    public function testHasDefaultSubCommands()
    {
        $config = new CommandConfig('command');
        $subConfig = new SubCommandConfig('sub');
        $subConfig->markDefault();

        $config->addSubCommandConfig($subConfig);

        $command = new Command($config);

        $this->assertTrue($command->hasDefaultSubCommands());
    }

    public function testHasNoDefaultSubCommands()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig('sub'));

        $command = new Command($config);

        $this->assertFalse($command->hasDefaultSubCommands());
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfNoSubCommandName()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig());

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfSubCommandSameNameAsOtherSubCommand()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig('sub'));
        $config->addSubCommandConfig(new SubCommandConfig('sub'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfSubCommandSameNameAsOptionCommandLong()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new OptionCommandConfig('option', 'o'));
        $config->addSubCommandConfig(new SubCommandConfig('option'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfSubCommandSameNameAsOptionCommandShort()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new OptionCommandConfig('option', 'o'));
        $config->addSubCommandConfig(new SubCommandConfig('o'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfOptionCommandSameNameAsOptionCommandLong()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new OptionCommandConfig('option', 'o'));
        $config->addSubCommandConfig(new OptionCommandConfig('option'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfOptionCommandSameNameAsOptionCommandShort()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new OptionCommandConfig('option1', 'o'));
        $config->addSubCommandConfig(new OptionCommandConfig('option2', 'o'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfOptionCommandSameNameAsOptionLong()
    {
        $config = new CommandConfig('command');
        $config->addOption('option');
        $config->addSubCommandConfig(new OptionCommandConfig('option'));

        new Command($config);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\CannotAddCommandException
     */
    public function testFailIfOptionCommandSameNameAsOptionShort()
    {
        $config = new CommandConfig('command');
        $config->addOption('option1', 'o');
        $config->addSubCommandConfig(new OptionCommandConfig('option2', 'o'));

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
        $io = $this->getMock('Webmozart\Console\Api\IO\IO');
        $handler = $this->getMock('stdClass', array('handle'));

        $config = new CommandConfig('command');
        $config->setHandler($handler);
        $command = new Command($config);

        $handler->expects($this->once())
            ->method('handle')
            ->with($args, $io, $command)
            ->willReturn(123);

        $this->assertSame(123, $command->handle($args, $io));
    }

    public function testHandleDispatchesEvent()
    {
        $args = new Args(new ArgsFormat());
        $io = $this->getMock('Webmozart\Console\Api\IO\IO');
        $handler = $this->getMock('stdClass', array('handle'));

        $this->application->getConfig()->addEventListener(ConsoleEvents::PRE_HANDLE, function (PreHandleEvent $event) {
            $event->setHandled(true);
            $event->setStatusCode(123);
        });

        $config = new CommandConfig('command');
        $config->setHandler($handler);
        $command = new Command($config, $this->application);

        $handler->expects($this->never())
            ->method('handle');

        $this->assertSame(123, $command->handle($args, $io));
    }

    public function testHandleWithCustomHandlerMethod()
    {
        $args = new Args(new ArgsFormat());
        $io = $this->getMock('Webmozart\Console\Api\IO\IO');
        $handler = $this->getMock('stdClass', array('handleFoo'));

        $config = new CommandConfig('command');
        $config->setHandler($handler);
        $config->setHandlerMethod('handleFoo');
        $command = new Command($config);

        $handler->expects($this->once())
            ->method('handleFoo')
            ->with($args, $io, $command)
            ->willReturn(123);

        $this->assertSame(123, $command->handle($args, $io));
    }

    public function testHandleConvertsEmptyStatusCodeToZero()
    {
        $args = new Args(new ArgsFormat());
        $io = $this->getMock('Webmozart\Console\Api\IO\IO');
        $handler = $this->getMock('stdClass', array('handle'));

        $config = new CommandConfig('command');
        $config->setHandler($handler);
        $command = new Command($config);

        $handler->expects($this->once())
            ->method('handle')
            ->with($args, $io, $command)
            ->willReturn(null);

        $this->assertSame(0, $command->handle($args, $io));
    }

    public function testHandleNormalizesNegativeStatusCodeToOne()
    {
        $args = new Args(new ArgsFormat());
        $io = $this->getMock('Webmozart\Console\Api\IO\IO');
        $handler = $this->getMock('stdClass', array('handle'));

        $config = new CommandConfig('command');
        $config->setHandler($handler);
        $command = new Command($config);

        $handler->expects($this->once())
            ->method('handle')
            ->with($args, $io, $command)
            ->willReturn(-1);

        // Negative status codes are not supported
        $this->assertSame(1, $command->handle($args, $io));
    }

    public function testHandleNormalizesNonNumericStatusCodeToOne()
    {
        $args = new Args(new ArgsFormat());
        $io = $this->getMock('Webmozart\Console\Api\IO\IO');
        $handler = $this->getMock('stdClass', array('handle'));

        $config = new CommandConfig('command');
        $config->setHandler($handler);
        $command = new Command($config);

        $handler->expects($this->once())
            ->method('handle')
            ->with($args, $io, $command)
            ->willReturn('foobar');

        $this->assertSame(1, $command->handle($args, $io));
    }

    public function testHandleNormalizesLargeStatusCodeToOne()
    {
        $args = new Args(new ArgsFormat());
        $io = $this->getMock('Webmozart\Console\Api\IO\IO');
        $handler = $this->getMock('stdClass', array('handle'));

        $config = new CommandConfig('command');
        $config->setHandler($handler);
        $command = new Command($config);

        $handler->expects($this->once())
            ->method('handle')
            ->with($args, $io, $command)
            ->willReturn(256);

        $this->assertSame(255, $command->handle($args, $io));
    }

    public function testRun()
    {
        $rawArgs = $this->getMock('Webmozart\Console\Api\Args\RawArgs');
        $parsedArgs = new Args(new ArgsFormat());
        $io = $this->getMock('Webmozart\Console\Api\IO\IO');
        $parser = $this->getMock('Webmozart\Console\Api\Args\ArgsParser');
        $handler = $this->getMock('stdClass', array('handle'));

        $config = new CommandConfig('command');
        $config->setArgsParser($parser);
        $config->setHandler($handler);
        $command = new Command($config);
        $format = $command->getArgsFormat();

        $parser->expects($this->once())
            ->method('parseArgs')
            ->with($this->identicalTo($rawArgs), $this->identicalTo($format), false)
            ->willReturn($parsedArgs);

        $handler->expects($this->once())
            ->method('handle')
            ->with($parsedArgs, $io, $command)
            ->willReturn(123);

        $this->assertSame(123, $command->run($rawArgs, $io));
    }

    public function testRunWithLenientArgsParsing()
    {
        $rawArgs = $this->getMock('Webmozart\Console\Api\Args\RawArgs');
        $parsedArgs = new Args(new ArgsFormat());
        $io = $this->getMock('Webmozart\Console\Api\IO\IO');
        $parser = $this->getMock('Webmozart\Console\Api\Args\ArgsParser');
        $handler = $this->getMock('stdClass', array('handle'));

        $config = new CommandConfig('command');
        $config->setArgsParser($parser);
        $config->setHandler($handler);
        $config->enableLenientArgsParsing();
        $command = new Command($config);
        $format = $command->getArgsFormat();

        $parser->expects($this->once())
            ->method('parseArgs')
            ->with($this->identicalTo($rawArgs), $this->identicalTo($format), true)
            ->willReturn($parsedArgs);

        $handler->expects($this->once())
            ->method('handle')
            ->with($parsedArgs, $io, $command)
            ->willReturn(123);

        $this->assertSame(123, $command->run($rawArgs, $io));
    }
}
