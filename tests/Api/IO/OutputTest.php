<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\IO;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Formatter\NullFormatter;
use Webmozart\Console\IO\OutputStream\BufferedOutputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class OutputTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var NullFormatter
     */
    private $formatter;

    /**
     * @var BufferedOutputStream
     */
    private $stream;

    /**
     * @var Output
     */
    private $output;

    protected function setUp()
    {
        $this->formatter = new NullFormatter();
        $this->stream = new BufferedOutputStream();
        $this->output = new Output($this->stream);
    }

    public function testWriteWhenNotVerbose()
    {
        $this->output->setVerbosity(IO::NORMAL);

        $this->output->write('Lorem ipsum ');
        $this->output->write('dolor sit amet ', IO::VERBOSE);
        $this->output->write('consetetur ', IO::VERY_VERBOSE);
        $this->output->write('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum ', $this->stream->fetch());
    }

    public function testWriteWhenVerbose()
    {
        $this->output->setVerbosity(IO::VERBOSE);

        $this->output->write('Lorem ipsum ');
        $this->output->write('dolor sit amet ', IO::VERBOSE);
        $this->output->write('consetetur ', IO::VERY_VERBOSE);
        $this->output->write('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet ', $this->stream->fetch());
    }

    public function testWriteWhenVeryVerbose()
    {
        $this->output->setVerbosity(IO::VERY_VERBOSE);

        $this->output->write('Lorem ipsum ');
        $this->output->write('dolor sit amet ', IO::VERBOSE);
        $this->output->write('consetetur ', IO::VERY_VERBOSE);
        $this->output->write('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur ', $this->stream->fetch());
    }

    public function testWriteWhenDebug()
    {
        $this->output->setVerbosity(IO::DEBUG);

        $this->output->write('Lorem ipsum ');
        $this->output->write('dolor sit amet ', IO::VERBOSE);
        $this->output->write('consetetur ', IO::VERY_VERBOSE);
        $this->output->write('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur sadipscing elitr', $this->stream->fetch());
    }

    public function testWriteFormatsText()
    {
        $formatter = $this->getMock('Webmozart\Console\Api\Formatter\Formatter');

        $formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->output->setFormatter($formatter);
        $this->output->write('<tag>text</tag>');

        $this->assertSame('<formatted>text</formatted>', $this->stream->fetch());
    }

    public function testWriteSupportsAnsiFormats()
    {
        $stream = $this->getMockBuilder('Webmozart\Console\IO\OutputStream\BufferedOutputStream')
            ->setMethods(array('supportsAnsi'))
            ->getMock();

        $stream->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(true);

        $ansiFormatter = $this->getMockBuilder('Webmozart\Console\Formatter\AnsiFormatter')
            ->setMethods(array('format', 'removeFormat'))
            ->getMock();

        $ansiFormatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->output->setStream($stream);
        $this->output->setFormatter($ansiFormatter);
        $this->output->write('<tag>text</tag>');

        $this->assertSame('<formatted>text</formatted>', $stream->fetch());
    }

    public function testWriteRemovesTagsIfAnsiNotSupported()
    {
        $stream = $this->getMockBuilder('Webmozart\Console\IO\OutputStream\BufferedOutputStream')
            ->setMethods(array('supportsAnsi'))
            ->getMock();

        $stream->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(false);

        $ansiFormatter = $this->getMockBuilder('Webmozart\Console\Formatter\AnsiFormatter')
            ->setMethods(array('format', 'removeFormat'))
            ->getMock();

        $ansiFormatter->expects($this->once())
            ->method('removeFormat')
            ->with('<tag>text</tag>')
            ->willReturn('text');

        $this->output->setStream($stream);
        $this->output->setFormatter($ansiFormatter);
        $this->output->write('<tag>text</tag>');

        $this->assertSame('text', $stream->fetch());
    }

    public function testWriteLineWhenNotVerbose()
    {
        $this->output->setVerbosity(IO::NORMAL);

        $this->output->writeLine('Lorem ipsum');
        $this->output->writeLine('dolor sit amet', IO::VERBOSE);
        $this->output->writeLine('consetetur', IO::VERY_VERBOSE);
        $this->output->writeLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\n", $this->stream->fetch());
    }

    public function testWriteLineWhenVerbose()
    {
        $this->output->setVerbosity(IO::VERBOSE);

        $this->output->writeLine('Lorem ipsum');
        $this->output->writeLine('dolor sit amet', IO::VERBOSE);
        $this->output->writeLine('consetetur', IO::VERY_VERBOSE);
        $this->output->writeLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\n", $this->stream->fetch());
    }

    public function testWriteLineWhenVeryVerbose()
    {
        $this->output->setVerbosity(IO::VERY_VERBOSE);

        $this->output->writeLine('Lorem ipsum');
        $this->output->writeLine('dolor sit amet', IO::VERBOSE);
        $this->output->writeLine('consetetur', IO::VERY_VERBOSE);
        $this->output->writeLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\n", $this->stream->fetch());
    }

    public function testWriteLineWhenDebug()
    {
        $this->output->setVerbosity(IO::DEBUG);

        $this->output->writeLine('Lorem ipsum');
        $this->output->writeLine('dolor sit amet', IO::VERBOSE);
        $this->output->writeLine('consetetur', IO::VERY_VERBOSE);
        $this->output->writeLine('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\nsadipscing elitr\n", $this->stream->fetch());
    }

    public function testWriteLineTrimsTrailingNewlines()
    {
        $this->output->writeLine("Lorem ipsum\n");

        $this->assertSame("Lorem ipsum\n", $this->stream->fetch());
    }

    public function testWriteLineDoesNotTrimTrailingSpaces()
    {
        $this->output->writeLine("Lorem ipsum   \n");

        $this->assertSame("Lorem ipsum   \n", $this->stream->fetch());
    }

    public function testWriteLineFormatsText()
    {
        $formatter = $this->getMock('Webmozart\Console\Api\Formatter\Formatter');

        $formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->output->setFormatter($formatter);
        $this->output->writeLine('<tag>text</tag>');

        $this->assertSame("<formatted>text</formatted>\n", $this->stream->fetch());
    }

    public function testWriteLineSupportsAnsiFormats()
    {
        $stream = $this->getMockBuilder('Webmozart\Console\IO\OutputStream\BufferedOutputStream')
            ->setMethods(array('supportsAnsi'))
            ->getMock();

        $stream->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(true);

        $ansiFormatter = $this->getMockBuilder('Webmozart\Console\Formatter\AnsiFormatter')
            ->setMethods(array('format', 'removeFormat'))
            ->getMock();

        $ansiFormatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->output->setStream($stream);
        $this->output->setFormatter($ansiFormatter);
        $this->output->writeLine('<tag>text</tag>');

        $this->assertSame("<formatted>text</formatted>\n", $stream->fetch());
    }

    public function testWriteLineRemovesTagsIfAnsiNotSupported()
    {
        $stream = $this->getMockBuilder('Webmozart\Console\IO\OutputStream\BufferedOutputStream')
            ->setMethods(array('supportsAnsi'))
            ->getMock();

        $stream->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(false);

        $ansiFormatter = $this->getMockBuilder('Webmozart\Console\Formatter\AnsiFormatter')
            ->setMethods(array('format', 'removeFormat'))
            ->getMock();

        $ansiFormatter->expects($this->once())
            ->method('removeFormat')
            ->with('<tag>text</tag>')
            ->willReturn('text');

        $this->output->setStream($stream);
        $this->output->setFormatter($ansiFormatter);
        $this->output->writeLine('<tag>text</tag>');

        $this->assertSame("text\n", $stream->fetch());
    }

    public function testWriteRawWhenNotVerbose()
    {
        $this->output->setVerbosity(IO::NORMAL);

        $this->output->writeRaw('Lorem ipsum ');
        $this->output->writeRaw('dolor sit amet ', IO::VERBOSE);
        $this->output->writeRaw('consetetur ', IO::VERY_VERBOSE);
        $this->output->writeRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum ', $this->stream->fetch());
    }

    public function testWriteRawWhenVerbose()
    {
        $this->output->setVerbosity(IO::VERBOSE);

        $this->output->writeRaw('Lorem ipsum ');
        $this->output->writeRaw('dolor sit amet ', IO::VERBOSE);
        $this->output->writeRaw('consetetur ', IO::VERY_VERBOSE);
        $this->output->writeRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet ', $this->stream->fetch());
    }

    public function testWriteRawWhenVeryVerbose()
    {
        $this->output->setVerbosity(IO::VERY_VERBOSE);

        $this->output->writeRaw('Lorem ipsum ');
        $this->output->writeRaw('dolor sit amet ', IO::VERBOSE);
        $this->output->writeRaw('consetetur ', IO::VERY_VERBOSE);
        $this->output->writeRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur ', $this->stream->fetch());
    }

    public function testWriteRawWhenDebug()
    {
        $this->output->setVerbosity(IO::DEBUG);

        $this->output->writeRaw('Lorem ipsum ');
        $this->output->writeRaw('dolor sit amet ', IO::VERBOSE);
        $this->output->writeRaw('consetetur ', IO::VERY_VERBOSE);
        $this->output->writeRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame('Lorem ipsum dolor sit amet consetetur sadipscing elitr', $this->stream->fetch());
    }

    public function testWriteRawDoesNotFormatText()
    {
        $formatter = $this->getMock('Webmozart\Console\Api\Formatter\Formatter');

        $formatter->expects($this->never())
            ->method('format');

        $this->output->setFormatter($formatter);
        $this->output->writeRaw('<tag>text</tag>');

        $this->assertSame('<tag>text</tag>', $this->stream->fetch());
    }

    public function testWriteLineRawWhenNotVerbose()
    {
        $this->output->setVerbosity(IO::NORMAL);

        $this->output->writeLineRaw('Lorem ipsum');
        $this->output->writeLineRaw('dolor sit amet', IO::VERBOSE);
        $this->output->writeLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->output->writeLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\n", $this->stream->fetch());
    }

    public function testWriteLineRawWhenVerbose()
    {
        $this->output->setVerbosity(IO::VERBOSE);

        $this->output->writeLineRaw('Lorem ipsum');
        $this->output->writeLineRaw('dolor sit amet', IO::VERBOSE);
        $this->output->writeLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->output->writeLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\n", $this->stream->fetch());
    }

    public function testWriteLineRawWhenVeryVerbose()
    {
        $this->output->setVerbosity(IO::VERY_VERBOSE);

        $this->output->writeLineRaw('Lorem ipsum');
        $this->output->writeLineRaw('dolor sit amet', IO::VERBOSE);
        $this->output->writeLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->output->writeLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\n", $this->stream->fetch());
    }

    public function testWriteLineRawWhenDebug()
    {
        $this->output->setVerbosity(IO::DEBUG);

        $this->output->writeLineRaw('Lorem ipsum');
        $this->output->writeLineRaw('dolor sit amet', IO::VERBOSE);
        $this->output->writeLineRaw('consetetur', IO::VERY_VERBOSE);
        $this->output->writeLineRaw('sadipscing elitr', IO::DEBUG);

        $this->assertSame("Lorem ipsum\ndolor sit amet\nconsetetur\nsadipscing elitr\n", $this->stream->fetch());
    }

    public function testWriteLineRawTrimsTrailingNewlines()
    {
        $this->output->writeLineRaw("Lorem ipsum\n");

        $this->assertSame("Lorem ipsum\n", $this->stream->fetch());
    }

    public function testWriteLineRawDoesNotTrimTrailingSpaces()
    {
        $this->output->writeLineRaw("Lorem ipsum   \n");

        $this->assertSame("Lorem ipsum   \n", $this->stream->fetch());
    }

    public function testWriteLineRawDoesNotFormatText()
    {
        $formatter = $this->getMock('Webmozart\Console\Api\Formatter\Formatter');

        $formatter->expects($this->never())
            ->method('format');

        $this->output->setFormatter($formatter);
        $this->output->writeLineRaw('<tag>text</tag>');

        $this->assertSame("<tag>text</tag>\n", $this->stream->fetch());
    }

    public function testFlush()
    {
        $stream = $this->getMock('Webmozart\Console\Api\IO\OutputStream');

        $stream->expects($this->once())
            ->method('flush');

        $this->output->setStream($stream);
        $this->output->flush();
    }

    public function testClose()
    {
        $stream = $this->getMock('Webmozart\Console\Api\IO\OutputStream');

        $stream->expects($this->once())
            ->method('close');

        $this->output->setStream($stream);
        $this->output->close();
    }
}
