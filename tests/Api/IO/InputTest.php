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

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\IO\InputStream\StringInputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputTest extends PHPUnit_Framework_TestCase
{
    const LOREM_IPSUM = "Lorem ipsum dolor sit amet,\nconsetetur sadipscing elitr,\nsed diam nonumy eirmod tempor invidunt";

    /**
     * @var StringInputStream
     */
    private $stream;

    /**
     * @var Input
     */
    private $input;

    protected function setUp()
    {
        $this->stream = new StringInputStream();
        $this->input = new Input($this->stream);
    }

    public function testRead()
    {
        $this->stream->set('Lorem ipsum');

        $this->assertSame('L', $this->input->read(1));
        $this->assertSame('orem ipsum', $this->input->read(20));
    }

    public function testReadReturnsDefaultIfNotInteractive()
    {
        $this->stream->set('Lorem ipsum');

        $this->input->setInteractive(false);

        $this->assertSame('Default', $this->input->read(20, 'Default'));
    }

    public function testReadLine()
    {
        $this->stream->set(self::LOREM_IPSUM);

        $this->assertSame("Lorem ipsum dolor sit amet,\n", $this->input->readLine());
        $this->assertSame('consetetu', $this->input->readLine(null, 10));
    }

    public function testReadLineReturnsDefaultIfNotInteractive()
    {
        $this->stream->set(self::LOREM_IPSUM);

        $this->input->setInteractive(false);

        $this->assertSame('Default', $this->input->readLine('Default'));
    }

    public function testIsInteractive()
    {
        $this->assertTrue($this->input->isInteractive());

        $this->input->setInteractive(false);

        $this->assertFalse($this->input->isInteractive());

        $this->input->setInteractive(true);

        $this->assertTrue($this->input->isInteractive());
    }

    public function testClose()
    {
        $stream = $this->getMock('Webmozart\Console\Api\IO\InputStream');
        $this->input = new Input($stream);

        $stream->expects($this->once())
            ->method('close');

        $this->input->close();
    }

    public function testIsClosed()
    {
        $stream = $this->getMock('Webmozart\Console\Api\IO\InputStream');
        $this->input = new Input($stream);

        $stream->expects($this->once())
            ->method('isClosed')
            ->willReturn('RESULT');

        $this->assertSame('RESULT', $this->input->isClosed());
    }
}
