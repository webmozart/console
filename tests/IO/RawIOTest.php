<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\IO;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\IO\Input\BufferedInput;
use Webmozart\Console\IO\Output\BufferedOutput;
use Webmozart\Console\IO\RawIO;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RawIOTest extends PHPUnit_Framework_TestCase
{
    const LOREM_IPSUM = "Lorem ipsum dolor sit amet,\nconsetetur sadipscing elitr,\nsed diam nonumy eirmod tempor invidunt";

    /**
     * @var BufferedInput
     */
    private $input;

    /**
     * @var BufferedOutput
     */
    private $errorOutput;

    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var RawIO
     */
    private $io;

    protected function setUp()
    {
        $this->input = new BufferedInput();
        $this->output = new BufferedOutput();
        $this->errorOutput = new BufferedOutput();
        $this->io = new RawIO($this->input, $this->output, $this->errorOutput);
    }

    public function testCreate()
    {
        $io = new RawIO($this->input, $this->output, $this->errorOutput);

        $this->assertSame($this->input, $io->getInput());
        $this->assertSame($this->output, $io->getOutput());
        $this->assertSame($this->errorOutput, $io->getErrorOutput());
    }

    public function testRead()
    {
        $this->input->set('Lorem ipsum');

        $this->assertSame('L', $this->io->read(1));
        $this->assertSame('orem ipsum', $this->io->read(20));
    }

    public function testReadReturnsDefaultIfNotInteractive()
    {
        $this->input->set('Lorem ipsum');

        $this->io->setInteractive(false);

        $this->assertSame('Default', $this->io->read(20, 'Default'));
    }

    public function testReadLine()
    {
        $this->input->set(self::LOREM_IPSUM);

        $this->assertSame("Lorem ipsum dolor sit amet,\n", $this->io->readLine());
        $this->assertSame('consetetu', $this->io->readLine(null, 10));
    }

    public function testReadLineReturnsDefaultIfNotInteractive()
    {
        $this->input->set(self::LOREM_IPSUM);

        $this->io->setInteractive(false);

        $this->assertSame('Default', $this->io->readLine('Default'));
    }

    public function testWriteWhenNotVerbose()
    {
        $this->io->setVerbosity(IO::NORMAL);

        $this->io->write('Lorem ipsum ');
        $this->io->write('dolor sit amet ', IO::VERBOSE);
        $this->io->write('consetetur ', IO::VERY_VERBOSE);
        $this->io->write('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum ', $this->output->fetch());
    }

    public function testWriteWhenVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $this->io->write('Lorem ipsum ');
        $this->io->write('dolor sit amet ', IO::VERBOSE);
        $this->io->write('consetetur ', IO::VERY_VERBOSE);
        $this->io->write('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet ', $this->output->fetch());
    }

    public function testWriteWhenVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $this->io->write('Lorem ipsum ');
        $this->io->write('dolor sit amet ', IO::VERBOSE);
        $this->io->write('consetetur ', IO::VERY_VERBOSE);
        $this->io->write('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur ', $this->output->fetch());
    }

    public function testWriteWhenDebug()
    {
        $this->io->setVerbosity(IO::DEBUG);

        $this->io->write('Lorem ipsum ');
        $this->io->write('dolor sit amet ', IO::VERBOSE);
        $this->io->write('consetetur ', IO::VERY_VERBOSE);
        $this->io->write('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur sadipscing elitr', $this->output->fetch());
    }

    public function testWriteLineWhenNotVerbose()
    {
        $this->io->setVerbosity(IO::NORMAL);

        $this->io->writeLine('Lorem ipsum');
        $this->io->writeLine('dolor sit amet', IO::VERBOSE);
        $this->io->writeLine('consetetur', IO::VERY_VERBOSE);
        $this->io->writeLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\n", $this->output->fetch());
    }

    public function testWriteLineWhenVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $this->io->writeLine('Lorem ipsum');
        $this->io->writeLine('dolor sit amet', IO::VERBOSE);
        $this->io->writeLine('consetetur', IO::VERY_VERBOSE);
        $this->io->writeLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\n", $this->output->fetch());
    }

    public function testWriteLineWhenVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $this->io->writeLine('Lorem ipsum');
        $this->io->writeLine('dolor sit amet', IO::VERBOSE);
        $this->io->writeLine('consetetur', IO::VERY_VERBOSE);
        $this->io->writeLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\n", $this->output->fetch());
    }

    public function testWriteLineWhenDebug()
    {
        $this->io->setVerbosity(IO::DEBUG);

        $this->io->writeLine('Lorem ipsum');
        $this->io->writeLine('dolor sit amet', IO::VERBOSE);
        $this->io->writeLine('consetetur', IO::VERY_VERBOSE);
        $this->io->writeLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\nsadipscing elitr\n", $this->output->fetch());
    }

    public function testWriteLineTrimsTrailingNewlines()
    {
        $this->io->writeLine("Lorem ipsum\n");

        $this->assertSame("Lorem ipsum\n", $this->output->fetch());
    }

    public function testWriteLineDoesNotTrimTrailingSpaces()
    {
        $this->io->writeLine("Lorem ipsum   \n");

        $this->assertSame("Lorem ipsum   \n", $this->output->fetch());
    }

    public function testWriteRawWhenNotVerbose()
    {
        $this->io->setVerbosity(IO::NORMAL);

        $this->io->writeRaw('Lorem ipsum ');
        $this->io->writeRaw('dolor sit amet ', IO::VERBOSE);
        $this->io->writeRaw('consetetur ', IO::VERY_VERBOSE);
        $this->io->writeRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum ', $this->output->fetch());
    }

    public function testWriteRawWhenVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $this->io->writeRaw('Lorem ipsum ');
        $this->io->writeRaw('dolor sit amet ', IO::VERBOSE);
        $this->io->writeRaw('consetetur ', IO::VERY_VERBOSE);
        $this->io->writeRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet ', $this->output->fetch());
    }

    public function testWriteRawWhenVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $this->io->writeRaw('Lorem ipsum ');
        $this->io->writeRaw('dolor sit amet ', IO::VERBOSE);
        $this->io->writeRaw('consetetur ', IO::VERY_VERBOSE);
        $this->io->writeRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur ', $this->output->fetch());
    }

    public function testWriteRawWhenDebug()
    {
        $this->io->setVerbosity(IO::DEBUG);

        $this->io->writeRaw('Lorem ipsum ');
        $this->io->writeRaw('dolor sit amet ', IO::VERBOSE);
        $this->io->writeRaw('consetetur ', IO::VERY_VERBOSE);
        $this->io->writeRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur sadipscing elitr', $this->output->fetch());
    }

    public function testWriteLineRawWhenNotVerbose()
    {
        $this->io->setVerbosity(IO::NORMAL);

        $this->io->writeLineRaw('Lorem ipsum');
        $this->io->writeLineRaw('dolor sit amet', IO::VERBOSE);
        $this->io->writeLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->io->writeLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\n", $this->output->fetch());
    }

    public function testWriteLineRawWhenVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $this->io->writeLineRaw('Lorem ipsum');
        $this->io->writeLineRaw('dolor sit amet', IO::VERBOSE);
        $this->io->writeLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->io->writeLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\n", $this->output->fetch());
    }

    public function testWriteLineRawWhenVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $this->io->writeLineRaw('Lorem ipsum');
        $this->io->writeLineRaw('dolor sit amet', IO::VERBOSE);
        $this->io->writeLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->io->writeLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\n", $this->output->fetch());
    }

    public function testWriteLineRawWhenDebug()
    {
        $this->io->setVerbosity(IO::DEBUG);

        $this->io->writeLineRaw('Lorem ipsum');
        $this->io->writeLineRaw('dolor sit amet', IO::VERBOSE);
        $this->io->writeLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->io->writeLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\nsadipscing elitr\n", $this->output->fetch());
    }

    public function testWriteLineRawTrimsTrailingNewlines()
    {
        $this->io->writeLineRaw("Lorem ipsum\n");

        $this->assertSame("Lorem ipsum\n", $this->output->fetch());
    }

    public function testWriteLineRawDoesNotTrimTrailingSpaces()
    {
        $this->io->writeLineRaw("Lorem ipsum   \n");

        $this->assertSame("Lorem ipsum   \n", $this->output->fetch());
    }

    public function testErrorWhenNotVerbose()
    {
        $this->io->setVerbosity(IO::NORMAL);

        $this->io->error('Lorem ipsum ');
        $this->io->error('dolor sit amet ', IO::VERBOSE);
        $this->io->error('consetetur ', IO::VERY_VERBOSE);
        $this->io->error('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum ', $this->errorOutput->fetch());
    }

    public function testErrorWhenVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $this->io->error('Lorem ipsum ');
        $this->io->error('dolor sit amet ', IO::VERBOSE);
        $this->io->error('consetetur ', IO::VERY_VERBOSE);
        $this->io->error('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet ', $this->errorOutput->fetch());
    }

    public function testErrorWhenVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $this->io->error('Lorem ipsum ');
        $this->io->error('dolor sit amet ', IO::VERBOSE);
        $this->io->error('consetetur ', IO::VERY_VERBOSE);
        $this->io->error('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur ', $this->errorOutput->fetch());
    }

    public function testErrorWhenDebug()
    {
        $this->io->setVerbosity(IO::DEBUG);

        $this->io->error('Lorem ipsum ');
        $this->io->error('dolor sit amet ', IO::VERBOSE);
        $this->io->error('consetetur ', IO::VERY_VERBOSE);
        $this->io->error('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur sadipscing elitr', $this->errorOutput->fetch());
    }

    public function testErrorLineWhenNotVerbose()
    {
        $this->io->setVerbosity(IO::NORMAL);

        $this->io->errorLine('Lorem ipsum');
        $this->io->errorLine('dolor sit amet', IO::VERBOSE);
        $this->io->errorLine('consetetur', IO::VERY_VERBOSE);
        $this->io->errorLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\n", $this->errorOutput->fetch());
    }

    public function testErrorLineWhenVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $this->io->errorLine('Lorem ipsum');
        $this->io->errorLine('dolor sit amet', IO::VERBOSE);
        $this->io->errorLine('consetetur', IO::VERY_VERBOSE);
        $this->io->errorLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\n", $this->errorOutput->fetch());
    }

    public function testErrorLineWhenVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $this->io->errorLine('Lorem ipsum');
        $this->io->errorLine('dolor sit amet', IO::VERBOSE);
        $this->io->errorLine('consetetur', IO::VERY_VERBOSE);
        $this->io->errorLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\n", $this->errorOutput->fetch());
    }

    public function testErrorLineWhenDebug()
    {
        $this->io->setVerbosity(IO::DEBUG);

        $this->io->errorLine('Lorem ipsum');
        $this->io->errorLine('dolor sit amet', IO::VERBOSE);
        $this->io->errorLine('consetetur', IO::VERY_VERBOSE);
        $this->io->errorLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\nsadipscing elitr\n", $this->errorOutput->fetch());
    }

    public function testErrorLineTrimsTrailingNewlines()
    {
        $this->io->errorLine("Lorem ipsum\n");

        $this->assertSame("Lorem ipsum\n", $this->errorOutput->fetch());
    }

    public function testErrorLineDoesNotTrimTrailingSpaces()
    {
        $this->io->errorLine("Lorem ipsum   \n");

        $this->assertSame("Lorem ipsum   \n", $this->errorOutput->fetch());
    }

    public function testErrorRawWhenNotVerbose()
    {
        $this->io->setVerbosity(IO::NORMAL);

        $this->io->errorRaw('Lorem ipsum ');
        $this->io->errorRaw('dolor sit amet ', IO::VERBOSE);
        $this->io->errorRaw('consetetur ', IO::VERY_VERBOSE);
        $this->io->errorRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum ', $this->errorOutput->fetch());
    }

    public function testErrorRawWhenVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $this->io->errorRaw('Lorem ipsum ');
        $this->io->errorRaw('dolor sit amet ', IO::VERBOSE);
        $this->io->errorRaw('consetetur ', IO::VERY_VERBOSE);
        $this->io->errorRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet ', $this->errorOutput->fetch());
    }

    public function testErrorRawWhenVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $this->io->errorRaw('Lorem ipsum ');
        $this->io->errorRaw('dolor sit amet ', IO::VERBOSE);
        $this->io->errorRaw('consetetur ', IO::VERY_VERBOSE);
        $this->io->errorRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur ', $this->errorOutput->fetch());
    }

    public function testErrorRawWhenDebug()
    {
        $this->io->setVerbosity(IO::DEBUG);

        $this->io->errorRaw('Lorem ipsum ');
        $this->io->errorRaw('dolor sit amet ', IO::VERBOSE);
        $this->io->errorRaw('consetetur ', IO::VERY_VERBOSE);
        $this->io->errorRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur sadipscing elitr', $this->errorOutput->fetch());
    }

    public function testErrorLineRawWhenNotVerbose()
    {
        $this->io->setVerbosity(IO::NORMAL);

        $this->io->errorLineRaw('Lorem ipsum');
        $this->io->errorLineRaw('dolor sit amet', IO::VERBOSE);
        $this->io->errorLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->io->errorLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\n", $this->errorOutput->fetch());
    }

    public function testErrorLineRawWhenVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $this->io->errorLineRaw('Lorem ipsum');
        $this->io->errorLineRaw('dolor sit amet', IO::VERBOSE);
        $this->io->errorLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->io->errorLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\n", $this->errorOutput->fetch());
    }

    public function testErrorLineRawWhenVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $this->io->errorLineRaw('Lorem ipsum');
        $this->io->errorLineRaw('dolor sit amet', IO::VERBOSE);
        $this->io->errorLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->io->errorLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\n", $this->errorOutput->fetch());
    }

    public function testErrorLineRawWhenDebug()
    {
        $this->io->setVerbosity(IO::DEBUG);

        $this->io->errorLineRaw('Lorem ipsum');
        $this->io->errorLineRaw('dolor sit amet', IO::VERBOSE);
        $this->io->errorLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->io->errorLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\nsadipscing elitr\n", $this->errorOutput->fetch());
    }

    public function testErrorLineRawTrimsTrailingNewlines()
    {
        $this->io->errorLineRaw("Lorem ipsum\n");

        $this->assertSame("Lorem ipsum\n", $this->errorOutput->fetch());
    }

    public function testErrorLineRawDoesNotTrimTrailingSpaces()
    {
        $this->io->errorLineRaw("Lorem ipsum   \n");

        $this->assertSame("Lorem ipsum   \n", $this->errorOutput->fetch());
    }

    public function testFormatDoesNothing()
    {
        $this->assertSame('Lorem ipsum', $this->io->format('Lorem ipsum'));
    }

    public function testRemoveFormatDoesNothing()
    {
        $this->assertSame('Lorem ipsum', $this->io->removeFormat('Lorem ipsum'));
    }

    public function testFlush()
    {
        $input = $this->getMock('Webmozart\Console\Api\IO\Input');
        $output = $this->getMock('Webmozart\Console\Api\IO\Output');
        $errorOutput = $this->getMock('Webmozart\Console\Api\IO\Output');
        $io = new RawIO($input, $output, $errorOutput);

        $output->expects($this->once())
            ->method('flush');
        $errorOutput->expects($this->once())
            ->method('flush');

        $io->flush();
    }

    public function testClose()
    {
        $input = $this->getMock('Webmozart\Console\Api\IO\Input');
        $output = $this->getMock('Webmozart\Console\Api\IO\Output');
        $errorOutput = $this->getMock('Webmozart\Console\Api\IO\Output');
        $io = new RawIO($input, $output, $errorOutput);

        $input->expects($this->once())
            ->method('close');
        $output->expects($this->once())
            ->method('close');
        $errorOutput->expects($this->once())
            ->method('close');

        $io->close();
    }
}
