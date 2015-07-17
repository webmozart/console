<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Handler\Help;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\ExecutableFinder;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Handler\Help\HelpAsciiDocHandler;
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\Process\ProcessLauncher;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpAsciiDocHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var Args
     */
    private $args;

    /**
     * @var BufferedIO
     */
    private $io;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ExecutableFinder
     */
    private $executableFinder;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ProcessLauncher
     */
    private $processLauncher;

    /**
     * @var HelpAsciiDocHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->path = __DIR__.'/Fixtures/ascii-doc/the-command.txt';
        $this->command = new Command(new CommandConfig('command'));
        $this->args = new Args(new ArgsFormat());
        $this->io = new BufferedIO();
        $this->executableFinder = $this->getMockBuilder('Symfony\Component\Process\ExecutableFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processLauncher = $this->getMockBuilder('Webmozart\Console\Process\ProcessLauncher')
            ->disableOriginalConstructor()
            ->getMock();
        $this->handler = new HelpAsciiDocHandler($this->path, $this->executableFinder, $this->processLauncher);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfPathNotFound()
    {
        new HelpAsciiDocHandler($this->path.'/foobar');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfBaseDirNoFile()
    {
        new HelpAsciiDocHandler($this->path.'/..');
    }

    public function testHandle()
    {
        $command = sprintf("less-binary '%s'", $this->path);

        $this->processLauncher->expects($this->once())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->handler->handle($this->args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    public function testHandlePrintsToOutputIfLessNotFound()
    {
        $this->processLauncher->expects($this->once())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue(null));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->handler->handle($this->args, $this->io, $this->command);

        $this->assertSame("Contents of the-command.txt\n", $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    public function testHandlePrintsToOutputIfProcessLauncherNotSupported()
    {
        $this->processLauncher->expects($this->once())
            ->method('isSupported')
            ->will($this->returnValue(false));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->handler->handle($this->args, $this->io, $this->command);

        $this->assertSame("Contents of the-command.txt\n", $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    public function testHandleWithCustomLessBinary()
    {
        $command = sprintf("my-less '%s'", $this->path);

        $this->processLauncher->expects($this->once())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->never())
            ->method('find');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $this->handler->setLessBinary('my-less');

        $status = $this->handler->handle($this->args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }
}
