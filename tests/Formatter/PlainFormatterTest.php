<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Formatter;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Api\Formatter\StyleSet;
use Webmozart\Console\Formatter\PlainFormatter;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PlainFormatterTest extends PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $formatter = new PlainFormatter(new StyleSet(array(
            Style::tag('bold')->bold(),
        )));

        $this->assertSame('<no-style>text</no-style>', $formatter->removeFormat('<bold><no-style>text</no-style></bold>'));
    }

    public function testRemoveFormat()
    {
        $formatter = new PlainFormatter(new StyleSet(array(
            Style::tag('bold')->bold(),
        )));

        $this->assertSame('<no-style>text</no-style>', $formatter->removeFormat('<bold><no-style>text</no-style></bold>'));
    }
}
