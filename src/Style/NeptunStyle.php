<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Style;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * A color style which prefers cyan for its good readability.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NeptunStyle
{
    /**
     * Adds styles to an output formatter.
     *
     * @param OutputFormatterInterface $formatter The output formatter.
     */
    public static function addStyles(OutputFormatterInterface $formatter)
    {
        $formatter->setStyle('h', new OutputFormatterStyle(null, null, array('bold')));
        $formatter->setStyle('b', new OutputFormatterStyle(null, null, array('bold')));
        $formatter->setStyle('em', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('tt', new OutputFormatterStyle(null, null, array('underscore')));
        $formatter->setStyle('warn', new OutputFormatterStyle('black', 'yellow'));
    }

    private function __construct() {}
}
