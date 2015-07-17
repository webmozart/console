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
use Webmozart\Console\Formatter\AnsiFormatter;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class AnsiFormatterTest extends PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $formatter = new AnsiFormatter(new StyleSet(array(
            Style::tag('bold')->bold(),
            Style::tag('yellow')->fgYellow(),
        )));

        $this->assertSame("\033[1mtext\033[22m", $formatter->format('<bold>text</bold>'));
    }

    public function testFormatWithStyle()
    {
        $formatter = new AnsiFormatter(new StyleSet(array(
            Style::tag('yellow')->fgYellow(),
        )));

        $this->assertSame("\033[1mtext\033[22m", $formatter->format('text', Style::noTag()->bold()));
    }

    public function testRemoveFormat()
    {
        $formatter = new AnsiFormatter(new StyleSet(array(
            Style::tag('bold')->bold(),
            Style::tag('yellow')->fgYellow(),
        )));

        $this->assertSame('<no-style>text</no-style>', $formatter->removeFormat('<no-style>text</no-style>'));
    }
}
