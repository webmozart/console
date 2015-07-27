<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\IO\InputStream;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\IO\InputStream\StreamInputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StreamInputStreamTest extends PHPUnit_Framework_TestCase
{
    const LOREM_IPSUM = "Lorem ipsum dolor sit amet,\nconsetetur sadipscing elitr,\nsed diam nonumy eirmod tempor invidunt";

    private $handle;

    protected function setUp()
    {
        $this->handle = fopen('php://memory', 'rw');

        fwrite($this->handle, self::LOREM_IPSUM);
        rewind($this->handle);
    }

    protected function tearDown()
    {
        @fclose($this->handle);
    }

    public function testRead()
    {
        $stream = new StreamInputStream($this->handle);

        $this->assertSame('L', $stream->read(1));
        $this->assertSame('o', $stream->read(1));
        $this->assertSame('rem ipsum dolor sit ', $stream->read(20));
        $this->assertSame("amet,\nconsetetur sadipscing elitr,\nsed diam nonumy eirmod tempor invidunt", $stream->read(100));
        $this->assertNull($stream->read(1));
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testReadFailsAfterClose()
    {
        $stream = new StreamInputStream($this->handle);
        $stream->close();

        $stream->read(1);
    }

    public function testReadLine()
    {
        $stream = new StreamInputStream($this->handle);

        $this->assertSame("Lorem ipsum dolor sit amet,\n", $stream->readLine());
        $this->assertSame('consetetu', $stream->readLine(10));
        $this->assertSame("r sadipscing elitr,\n", $stream->readLine(100));
        $this->assertSame('sed diam nonumy eirmod tempor invidunt', $stream->readLine());
        $this->assertNull($stream->readLine());
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testReadLineFailsAfterClose()
    {
        $stream = new StreamInputStream($this->handle);
        $stream->close();

        $stream->readLine();
    }

    public function testIgnoreDuplicateClose()
    {
        $stream = new StreamInputStream($this->handle);
        $stream->close();
        $stream->close();
    }
}
