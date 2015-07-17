<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\IO\Input;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\IO\Input\BufferedInput;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BufferedInputTest extends PHPUnit_Framework_TestCase
{
    const LOREM_IPSUM = "Lorem ipsum dolor sit amet,\nconsetetur sadipscing elitr,\nsed diam nonumy eirmod tempor invidunt";

    public function testRead()
    {
        $input = new BufferedInput(self::LOREM_IPSUM);

        $this->assertSame('L', $input->read(1));
        $this->assertSame('o', $input->read(1));
        $this->assertSame('rem ipsum dolor sit ', $input->read(20));
        $this->assertSame("amet,\nconsetetur sadipscing elitr,\nsed diam nonumy eirmod tempor invidunt", $input->read(100));
        $this->assertNull($input->read(1));
    }

    public function testReadEmpty()
    {
        $input = new BufferedInput();

        $this->assertNull($input->read(1));
    }

    public function testReadLine()
    {
        $input = new BufferedInput(self::LOREM_IPSUM);

        $this->assertSame("Lorem ipsum dolor sit amet,\n", $input->readLine());
        $this->assertSame('consetetu', $input->readLine(10));
        $this->assertSame("r sadipscing elitr,\n", $input->readLine(100));
        $this->assertSame('sed diam nonumy eirmod tempor invidunt', $input->readLine());
        $this->assertNull($input->readLine());
    }

    public function testReadLineEmpty()
    {
        $input = new BufferedInput();

        $this->assertNull($input->readLine());
    }

    public function testClear()
    {
        $input = new BufferedInput(self::LOREM_IPSUM);

        $this->assertSame("Lorem ipsum dolor sit amet,\n", $input->readLine());

        $input->clear();

        $this->assertNull($input->readLine());
    }

    public function testAppend()
    {
        $input = new BufferedInput("Lorem\nIpsum\n");

        $this->assertSame("Lorem\n", $input->readLine());

        $input->append("Dolor\n");

        $this->assertSame("Ipsum\n", $input->readLine());
        $this->assertSame("Dolor\n", $input->readLine());
        $this->assertNull($input->readLine());
    }

    public function testSet()
    {
        $input = new BufferedInput(self::LOREM_IPSUM);

        $this->assertSame("Lorem ipsum dolor sit amet,\n", $input->readLine());

        $input->set('Foobar');

        $this->assertSame('Foobar', $input->readLine());
        $this->assertNull($input->readLine());
    }
}
