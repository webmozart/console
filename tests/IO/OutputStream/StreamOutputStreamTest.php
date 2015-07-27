<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\IO\OutputStream;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\IO\OutputStream\StreamOutputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StreamOutputStreamTest extends PHPUnit_Framework_TestCase
{
    private $handle;

    protected function setUp()
    {
        $this->handle = fopen('php://memory', 'rw');
    }

    protected function tearDown()
    {
        @fclose($this->handle);
    }

    public function testWrite()
    {
        $stream = new StreamOutputStream($this->handle);
        $stream->write('Lorem ipsum');

        rewind($this->handle);

        $this->assertSame('Lorem ipsum', fread($this->handle, 20));
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testWriteFailsAfterClose()
    {
        $stream = new StreamOutputStream($this->handle);
        $stream->close();
        $stream->write('Lorem ipsum');
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testFlushFailsAfterClose()
    {
        $stream = new StreamOutputStream($this->handle);
        $stream->close();
        $stream->flush();
    }

    public function testIgnoreDuplicateClose()
    {
        $stream = new StreamOutputStream($this->handle);
        $stream->close();
        $stream->close();
    }
}
