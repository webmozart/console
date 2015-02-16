<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\IO\Output;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\IO\Output\BufferedOutput;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BufferedOutputTest extends PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $output = new BufferedOutput();
        $output->write('Lorem ipsum');

        $this->assertSame('Lorem ipsum', $output->fetch());
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testWriteFailsAfterClose()
    {
        $output = new BufferedOutput();
        $output->close();
        $output->write('Lorem ipsum');
    }

    public function testFetchAfterClose()
    {
        $output = new BufferedOutput();
        $output->write('Lorem ipsum');
        $output->close();

        $this->assertSame('Lorem ipsum', $output->fetch());
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testFlushFailsAfterClose()
    {
        $output = new BufferedOutput();
        $output->close();
        $output->flush();
    }

    public function testIgnoreDuplicateClose()
    {
        $output = new BufferedOutput();
        $output->close();
        $output->close();
    }

    public function testClear()
    {
        $output = new BufferedOutput();
        $output->write('Lorem');
        $output->clear();
        $output->write('ipsum');
        $output->close();

        $this->assertSame('ipsum', $output->fetch());
    }
}
