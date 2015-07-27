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
use Webmozart\Console\IO\OutputStream\BufferedOutputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BufferedOutputStreamTest extends PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $stream = new BufferedOutputStream();
        $stream->write('Lorem ipsum');

        $this->assertSame('Lorem ipsum', $stream->fetch());
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testWriteFailsAfterClose()
    {
        $stream = new BufferedOutputStream();
        $stream->close();
        $stream->write('Lorem ipsum');
    }

    public function testFetchAfterClose()
    {
        $stream = new BufferedOutputStream();
        $stream->write('Lorem ipsum');
        $stream->close();

        $this->assertSame('Lorem ipsum', $stream->fetch());
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testFlushFailsAfterClose()
    {
        $stream = new BufferedOutputStream();
        $stream->close();
        $stream->flush();
    }

    public function testIgnoreDuplicateClose()
    {
        $stream = new BufferedOutputStream();
        $stream->close();
        $stream->close();
    }

    public function testClear()
    {
        $stream = new BufferedOutputStream();
        $stream->write('Lorem');
        $stream->clear();
        $stream->write('ipsum');
        $stream->close();

        $this->assertSame('ipsum', $stream->fetch());
    }
}
