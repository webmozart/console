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
use Webmozart\Console\IO\InputStream\StringInputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StringInputStreamTest extends PHPUnit_Framework_TestCase
{
    const LOREM_IPSUM = "Lorem ipsum dolor sit amet,\nconsetetur sadipscing elitr,\nsed diam nonumy eirmod tempor invidunt";

    public function testRead()
    {
        $stream = new StringInputStream(self::LOREM_IPSUM);

        $this->assertSame('L', $stream->read(1));
        $this->assertSame('o', $stream->read(1));
        $this->assertSame('rem ipsum dolor sit ', $stream->read(20));
        $this->assertSame("amet,\nconsetetur sadipscing elitr,\nsed diam nonumy eirmod tempor invidunt", $stream->read(100));
        $this->assertNull($stream->read(1));
    }

    public function testReadEmpty()
    {
        $stream = new StringInputStream();

        $this->assertNull($stream->read(1));
    }

    public function testReadLine()
    {
        $stream = new StringInputStream(self::LOREM_IPSUM);

        $this->assertSame("Lorem ipsum dolor sit amet,\n", $stream->readLine());
        $this->assertSame('consetetu', $stream->readLine(10));
        $this->assertSame("r sadipscing elitr,\n", $stream->readLine(100));
        $this->assertSame('sed diam nonumy eirmod tempor invidunt', $stream->readLine());
        $this->assertNull($stream->readLine());
    }

    public function testReadLineEmpty()
    {
        $stream = new StringInputStream();

        $this->assertNull($stream->readLine());
    }

    public function testClear()
    {
        $stream = new StringInputStream(self::LOREM_IPSUM);

        $this->assertSame("Lorem ipsum dolor sit amet,\n", $stream->readLine());

        $stream->clear();

        $this->assertNull($stream->readLine());
    }

    public function testAppend()
    {
        $stream = new StringInputStream("Lorem\nIpsum\n");

        $this->assertSame("Lorem\n", $stream->readLine());

        $stream->append("Dolor\n");

        $this->assertSame("Ipsum\n", $stream->readLine());
        $this->assertSame("Dolor\n", $stream->readLine());
        $this->assertNull($stream->readLine());
    }

    public function testSet()
    {
        $stream = new StringInputStream(self::LOREM_IPSUM);

        $this->assertSame("Lorem ipsum dolor sit amet,\n", $stream->readLine());

        $stream->set('Foobar');

        $this->assertSame('Foobar', $stream->readLine());
        $this->assertNull($stream->readLine());
    }
}
