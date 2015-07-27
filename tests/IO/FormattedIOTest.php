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

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Formatter\AnsiFormatter;
use Webmozart\Console\IO\FormattedIO;
use Webmozart\Console\IO\InputStream\StringInputStream;
use Webmozart\Console\IO\OutputStream\BufferedOutputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormattedIOTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Formatter
     */
    private $formatter;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|AnsiFormatter
     */
    private $ansiFormatter;

    /**
     * @var StringInputStream
     */
    private $input;

    /**
     * @var BufferedOutputStream
     */
    private $output;

    /**
     * @var BufferedOutputStream
     */
    private $errorOutput;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|BufferedOutputStream
     */
    private $ansiOutput;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|BufferedOutputStream
     */
    private $noAnsiOutput;

    /**
     * @var FormattedIO
     */
    private $io;

    protected function setUp()
    {
        $this->formatter = $this->getMock('Webmozart\Console\Api\Formatter\Formatter');
        $this->ansiFormatter = $this->getMockBuilder('Webmozart\Console\Formatter\AnsiFormatter')
            ->setMethods(array('format', 'removeFormat'))
            ->getMock();
        $this->input = new StringInputStream();
        $this->output = new BufferedOutputStream();
        $this->errorOutput = new BufferedOutputStream();
        $this->io = new FormattedIO($this->input, $this->output, $this->errorOutput, $this->formatter);

        $this->ansiOutput = $this->getMockBuilder('Webmozart\Console\IO\OutputStream\BufferedOutputStream')
            ->setMethods(array('supportsAnsi'))
            ->getMock();

        $this->ansiOutput->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(true);

        $this->noAnsiOutput = $this->getMockBuilder('Webmozart\Console\IO\OutputStream\BufferedOutputStream')
            ->setMethods(array('supportsAnsi'))
            ->getMock();

        $this->noAnsiOutput->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(false);
    }

    public function testWrite()
    {
        $this->formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->write('<tag>text</tag>');

        $this->assertSame('<formatted>text</formatted>', $this->output->fetch());
    }

    public function testWriteAnsi()
    {
        $this->io = new FormattedIO($this->input, $this->ansiOutput, $this->errorOutput, $this->ansiFormatter);

        $this->ansiFormatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->write('<tag>text</tag>');

        $this->assertSame('<formatted>text</formatted>', $this->ansiOutput->fetch());
    }

    public function testWriteRemovesTagsIfAnsiNotSupported()
    {
        $this->io = new FormattedIO($this->input, $this->noAnsiOutput, $this->errorOutput, $this->ansiFormatter);

        $this->ansiFormatter->expects($this->once())
            ->method('removeFormat')
            ->with('<tag>text</tag>')
            ->willReturn('text');

        $this->io->write('<tag>text</tag>');

        $this->assertSame('text', $this->noAnsiOutput->fetch());
    }

    public function testWriteLine()
    {
        $this->formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->writeLine('<tag>text</tag>');

        $this->assertSame("<formatted>text</formatted>\n", $this->output->fetch());
    }

    public function testWriteLineTrimsTrailingNewline()
    {
        $this->formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->writeLine("<tag>text</tag>\n");

        $this->assertSame("<formatted>text</formatted>\n", $this->output->fetch());
    }

    public function testWriteLineDoesNotTrimTrailingSpaces()
    {
        $this->formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>   ')
            ->willReturn('<formatted>text</formatted>   ');

        $this->io->writeLine('<tag>text</tag>   ');

        $this->assertSame("<formatted>text</formatted>   \n", $this->output->fetch());
    }

    public function testWriteLineAnsi()
    {
        $this->io = new FormattedIO($this->input, $this->ansiOutput, $this->errorOutput, $this->ansiFormatter);

        $this->ansiFormatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->writeLine('<tag>text</tag>');

        $this->assertSame("<formatted>text</formatted>\n", $this->ansiOutput->fetch());
    }

    public function testWriteLineRemovesTagsIfAnsiNotSupported()
    {
        $this->io = new FormattedIO($this->input, $this->noAnsiOutput, $this->errorOutput, $this->ansiFormatter);

        $this->ansiFormatter->expects($this->once())
            ->method('removeFormat')
            ->with('<tag>text</tag>')
            ->willReturn('text');

        $this->io->writeLine('<tag>text</tag>');

        $this->assertSame("text\n", $this->noAnsiOutput->fetch());
    }

    public function testError()
    {
        $this->formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->error('<tag>text</tag>');

        $this->assertSame('<formatted>text</formatted>', $this->errorOutput->fetch());
    }

    public function testErrorAnsi()
    {
        $this->io = new FormattedIO($this->input, $this->output, $this->ansiOutput, $this->ansiFormatter);

        $this->ansiFormatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->error('<tag>text</tag>');

        $this->assertSame('<formatted>text</formatted>', $this->ansiOutput->fetch());
    }

    public function testErrorRemovesTagsIfAnsiNotSupported()
    {
        $this->io = new FormattedIO($this->input, $this->output, $this->noAnsiOutput, $this->ansiFormatter);

        $this->ansiFormatter->expects($this->once())
            ->method('removeFormat')
            ->with('<tag>text</tag>')
            ->willReturn('text');

        $this->io->error('<tag>text</tag>');

        $this->assertSame('text', $this->noAnsiOutput->fetch());
    }

    public function testErrorLine()
    {
        $this->formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->errorLine('<tag>text</tag>');

        $this->assertSame("<formatted>text</formatted>\n", $this->errorOutput->fetch());
    }

    public function testErrorLineTrimsTrailingNewline()
    {
        $this->formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->errorLine("<tag>text</tag>\n");

        $this->assertSame("<formatted>text</formatted>\n", $this->errorOutput->fetch());
    }

    public function testErrorLineDoesNotTrimTrailingSpaces()
    {
        $this->formatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>   ')
            ->willReturn('<formatted>text</formatted>   ');

        $this->io->errorLine('<tag>text</tag>   ');

        $this->assertSame("<formatted>text</formatted>   \n", $this->errorOutput->fetch());
    }

    public function testErrorLineAnsi()
    {
        $this->io = new FormattedIO($this->input, $this->output, $this->ansiOutput, $this->ansiFormatter);

        $this->ansiFormatter->expects($this->once())
            ->method('format')
            ->with('<tag>text</tag>')
            ->willReturn('<formatted>text</formatted>');

        $this->io->errorLine('<tag>text</tag>');

        $this->assertSame("<formatted>text</formatted>\n", $this->ansiOutput->fetch());
    }

    public function testErrorLineRemovesTagsIfAnsiNotSupported()
    {
        $this->io = new FormattedIO($this->input, $this->output, $this->noAnsiOutput, $this->ansiFormatter);

        $this->ansiFormatter->expects($this->once())
            ->method('removeFormat')
            ->with('<tag>text</tag>')
            ->willReturn('text');

        $this->io->errorLine('<tag>text</tag>');

        $this->assertSame("text\n", $this->noAnsiOutput->fetch());
    }

    public function testFormat()
    {
        $this->formatter->expects($this->once())
            ->method('format')
            ->with('input')
            ->willReturn('output');

        $this->assertSame('output', $this->io->format('input'));
    }

    public function testRemoveFormat()
    {
        $this->formatter->expects($this->once())
            ->method('removeFormat')
            ->with('input')
            ->willReturn('output');

        $this->assertSame('output', $this->io->removeFormat('input'));
    }
}
