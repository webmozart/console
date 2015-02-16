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
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Webmozart\Console\Adapter\StyleConverter;
use Webmozart\Console\Api\Formatter\Style;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StyleConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestCases
     */
    public function testConvert($style, $converted)
    {
        $this->assertEquals($converted, StyleConverter::convert($style));
    }

    public function getTestCases()
    {
        return array(
            array(
                Style::tag('tag'),
                new OutputFormatterStyle(),
            ),
            array(
                Style::noTag(),
                new OutputFormatterStyle(),
            ),
            array(
                Style::noTag()->fgBlack(),
                new OutputFormatterStyle('black'),
            ),
            array(
                Style::noTag()->fgBlue(),
                new OutputFormatterStyle('blue'),
            ),
            array(
                Style::noTag()->fgCyan(),
                new OutputFormatterStyle('cyan'),
            ),
            array(
                Style::noTag()->fgGreen(),
                new OutputFormatterStyle('green'),
            ),
            array(
                Style::noTag()->fgMagenta(),
                new OutputFormatterStyle('magenta'),
            ),
            array(
                Style::noTag()->fgRed(),
                new OutputFormatterStyle('red'),
            ),
            array(
                Style::noTag()->fgWhite(),
                new OutputFormatterStyle('white'),
            ),
            array(
                Style::noTag()->fgYellow(),
                new OutputFormatterStyle('yellow'),
            ),
            array(
                Style::noTag()->bgBlack(),
                new OutputFormatterStyle(null, 'black'),
            ),
            array(
                Style::noTag()->bgBlue(),
                new OutputFormatterStyle(null, 'blue'),
            ),
            array(
                Style::noTag()->bgCyan(),
                new OutputFormatterStyle(null, 'cyan'),
            ),
            array(
                Style::noTag()->bgGreen(),
                new OutputFormatterStyle(null, 'green'),
            ),
            array(
                Style::noTag()->bgMagenta(),
                new OutputFormatterStyle(null, 'magenta'),
            ),
            array(
                Style::noTag()->bgRed(),
                new OutputFormatterStyle(null, 'red'),
            ),
            array(
                Style::noTag()->bgWhite(),
                new OutputFormatterStyle(null, 'white'),
            ),
            array(
                Style::noTag()->bgYellow(),
                new OutputFormatterStyle(null, 'yellow'),
            ),
            array(
                Style::noTag()->bold(),
                new OutputFormatterStyle(null, null, array('bold')),
            ),
            array(
                Style::noTag()->underlined(),
                new OutputFormatterStyle(null, null, array('underscore')),
            ),
            array(
                Style::noTag()->reversed(),
                new OutputFormatterStyle(null, null, array('reverse')),
            ),
            array(
                Style::noTag()->blinking(),
                new OutputFormatterStyle(null, null, array('blink')),
            ),
            array(
                Style::noTag()->concealed(),
                new OutputFormatterStyle(null, null, array('conceal')),
            ),
            array(
                Style::noTag()->fgWhite()->bgBlack()->bold()->concealed(),
                new OutputFormatterStyle('white', 'black', array('bold', 'conceal')),
            ),
        );
    }
}
