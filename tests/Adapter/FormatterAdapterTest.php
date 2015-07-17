<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Adapter;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Adapter\FormatterAdapter;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormatterAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testFormatDecorated()
    {
        $formatter = $this->getMock('Webmozart\Console\Api\Formatter\Formatter');

        $formatter->expects($this->once())
            ->method('format')
            ->with('text')
            ->willReturn('formatted');

        $adapter = new FormatterAdapter($formatter);
        $adapter->setDecorated(true);

        $this->assertSame('formatted', $adapter->format('text'));
    }

    public function testFormatNonDecorated()
    {
        $formatter = $this->getMock('Webmozart\Console\Api\Formatter\Formatter');

        $formatter->expects($this->once())
            ->method('removeFormat')
            ->with('text')
            ->willReturn('unformatted');

        $adapter = new FormatterAdapter($formatter);
        $adapter->setDecorated(false);

        $this->assertSame('unformatted', $adapter->format('text'));
    }

    public function testIsDecorated()
    {
        $formatter = $this->getMock('Webmozart\Console\Api\Formatter\Formatter');
        $adapter = new FormatterAdapter($formatter);

        $this->assertTrue($adapter->isDecorated());

        $adapter->setDecorated(false);

        $this->assertFalse($adapter->isDecorated());
    }

    public function testGetAdaptedFormatter()
    {
        $formatter = $this->getMock('Webmozart\Console\Api\Formatter\Formatter');
        $adapter = new FormatterAdapter($formatter);

        $this->assertSame($formatter, $adapter->getAdaptedFormatter());
    }
}
