<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Adapter;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Adapter\FormatterAdapter;
use Webmozart\Console\Adapter\IOOutput;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\IO\InputStream\StringInputStream;
use Webmozart\Console\IO\OutputStream\BufferedOutputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class IOOutputTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|IO
     */
    private $io;

    /**
     * @var IOOutput
     */
    private $output;

    protected function setUp()
    {
        $this->io = $this->getMockBuilder('Webmozart\Console\Api\IO\IO')
            ->disableOriginalConstructor()
            ->getMock();
        $this->output = new IOOutput($this->io);
    }

    public function testWriteSingleMessage()
    {
        $this->io->expects($this->once())
            ->method('write')
            ->with('message');

        $this->output->write('message');
    }

    public function testWriteSingleMessageWithNewline()
    {
        $this->io->expects($this->once())
            ->method('writeLine')
            ->with('message');

        $this->output->write('message', true);
    }

    public function testWriteMultipleMessages()
    {
        $this->io->expects($this->at(0))
            ->method('write')
            ->with('message1');
        $this->io->expects($this->at(1))
            ->method('write')
            ->with('message2');

        $this->output->write(array('message1', 'message2'));
    }

    public function testWriteMultipleMessagesWithNewline()
    {
        $this->io->expects($this->at(0))
            ->method('writeLine')
            ->with('message1');
        $this->io->expects($this->at(1))
            ->method('writeLine')
            ->with('message2');

        $this->output->write(array('message1', 'message2'), true);
    }

    public function testWritePlain()
    {
        $this->io->expects($this->once())
            ->method('removeFormat')
            ->with('message')
            ->willReturn('unformatted');

        $this->io->expects($this->once())
            ->method('write')
            ->with('unformatted');

        $this->output->write('message', false, OutputInterface::OUTPUT_PLAIN);
    }

    public function testWriteRaw()
    {
        $this->io->expects($this->once())
            ->method('writeRaw')
            ->with('message');

        $this->output->write('message', false, OutputInterface::OUTPUT_RAW);
    }

    public function testWriteLineSingleMessage()
    {
        $this->io->expects($this->once())
            ->method('writeLine')
            ->with('message');

        $this->output->writeln('message');
    }

    public function testWriteLineMultipleMessages()
    {
        $this->io->expects($this->at(0))
            ->method('writeLine')
            ->with('message1');
        $this->io->expects($this->at(1))
            ->method('writeLine')
            ->with('message2');

        $this->output->writeln(array('message1', 'message2'));
    }

    public function testSetVerbosityNormal()
    {
        $this->io = $this->getMockBuilder('Webmozart\Console\Api\IO\IO')
            ->disableOriginalConstructor()
            ->getMock();
        $this->output = new IOOutput($this->io);

        $this->io->expects($this->once())
            ->method('setQuiet')
            ->with(false);
        $this->io->expects($this->once())
            ->method('setVerbosity')
            ->with(IO::NORMAL);

        $this->output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
    }

    public function testSetVerbosityVerbose()
    {
        $this->io = $this->getMockBuilder('Webmozart\Console\Api\IO\IO')
            ->disableOriginalConstructor()
            ->getMock();
        $this->output = new IOOutput($this->io);

        $this->io->expects($this->once())
            ->method('setQuiet')
            ->with(false);
        $this->io->expects($this->once())
            ->method('setVerbosity')
            ->with(IO::VERBOSE);

        $this->output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
    }

    public function testSetVerbosityVeryVerbose()
    {
        $this->io = $this->getMockBuilder('Webmozart\Console\Api\IO\IO')
            ->disableOriginalConstructor()
            ->getMock();
        $this->output = new IOOutput($this->io);

        $this->io->expects($this->once())
            ->method('setQuiet')
            ->with(false);
        $this->io->expects($this->once())
            ->method('setVerbosity')
            ->with(IO::VERY_VERBOSE);

        $this->output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
    }

    public function testSetVerbosityDebug()
    {
        $this->io = $this->getMockBuilder('Webmozart\Console\Api\IO\IO')
            ->disableOriginalConstructor()
            ->getMock();
        $this->output = new IOOutput($this->io);

        $this->io->expects($this->once())
            ->method('setQuiet')
            ->with(false);
        $this->io->expects($this->once())
            ->method('setVerbosity')
            ->with(IO::DEBUG);

        $this->output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
    }

    public function testSetVerbosityQuiet()
    {
        $this->io = $this->getMockBuilder('Webmozart\Console\Api\IO\IO')
            ->disableOriginalConstructor()
            ->getMock();
        $this->output = new IOOutput($this->io);

        $this->io->expects($this->once())
            ->method('setQuiet')
            ->with(true);

        $this->output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
    }

    public function testGetVerbosityNormal()
    {
        $input = new Input(new StringInputStream());
        $output = new Output(new BufferedOutputStream());
        $errorOutput = new Output(new BufferedOutputStream());
        $this->io = new IO($input, $output, $errorOutput);
        $this->output = new IOOutput($this->io);

        $this->io->setQuiet(false);
        $this->io->setVerbosity(IO::NORMAL);

        $this->assertSame(OutputInterface::VERBOSITY_NORMAL, $this->output->getVerbosity());
    }

    public function testGetVerbosityVerbose()
    {
        $input = new Input(new StringInputStream());
        $output = new Output(new BufferedOutputStream());
        $errorOutput = new Output(new BufferedOutputStream());
        $this->io = new IO($input, $output, $errorOutput);
        $this->output = new IOOutput($this->io);

        $this->io->setQuiet(false);
        $this->io->setVerbosity(IO::VERBOSE);

        $this->assertSame(OutputInterface::VERBOSITY_VERBOSE, $this->output->getVerbosity());
    }

    public function testGetVerbosityVeryVerbose()
    {
        $input = new Input(new StringInputStream());
        $output = new Output(new BufferedOutputStream());
        $errorOutput = new Output(new BufferedOutputStream());
        $this->io = new IO($input, $output, $errorOutput);
        $this->output = new IOOutput($this->io);

        $this->io->setQuiet(false);
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $this->assertSame(OutputInterface::VERBOSITY_VERY_VERBOSE, $this->output->getVerbosity());
    }

    public function testGetVerbosityDebug()
    {
        $input = new Input(new StringInputStream());
        $output = new Output(new BufferedOutputStream());
        $errorOutput = new Output(new BufferedOutputStream());
        $this->io = new IO($input, $output, $errorOutput);
        $this->output = new IOOutput($this->io);

        $this->io->setQuiet(false);
        $this->io->setVerbosity(IO::DEBUG);

        $this->assertSame(OutputInterface::VERBOSITY_DEBUG, $this->output->getVerbosity());
    }

    public function testGetVerbosityQuiet()
    {
        $input = new Input(new StringInputStream());
        $output = new Output(new BufferedOutputStream());
        $errorOutput = new Output(new BufferedOutputStream());
        $this->io = new IO($input, $output, $errorOutput);
        $this->output = new IOOutput($this->io);

        $this->io->setQuiet(true);

        $this->assertSame(OutputInterface::VERBOSITY_QUIET, $this->output->getVerbosity());
    }

    public function testGetFormatter()
    {
        $this->assertEquals(new FormatterAdapter($this->io), $this->output->getFormatter());
    }
}
