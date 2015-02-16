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
use Webmozart\Console\IO\Output\StreamOutput;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StreamOutputTest extends PHPUnit_Framework_TestCase
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
        $output = new StreamOutput($this->handle);
        $output->write('Lorem ipsum');

        rewind($this->handle);

        $this->assertSame('Lorem ipsum', fread($this->handle, 20));
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testWriteFailsAfterClose()
    {
        $output = new StreamOutput($this->handle);
        $output->close();
        $output->write('Lorem ipsum');
    }

    /**
     * @expectedException \Webmozart\Console\Api\IO\IOException
     */
    public function testFlushFailsAfterClose()
    {
        $output = new StreamOutput($this->handle);
        $output->close();
        $output->flush();
    }

    public function testIgnoreDuplicateClose()
    {
        $output = new StreamOutput($this->handle);
        $output->close();
        $output->close();
    }
}
