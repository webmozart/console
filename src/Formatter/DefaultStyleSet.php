<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Formatter;

use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Api\Formatter\StyleSet;

/**
 * A color style which prefers cyan for its good readability.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultStyleSet extends StyleSet
{
    public function __construct()
    {
        $this->replace(array(
            // Symfony default styles
            Style::tag('error')->fgWhite()->bgRed(),
            Style::tag('info')->fgGreen(),
            Style::tag('comment')->fgYellow(),
            Style::tag('question')->fgBlack()->bgCyan(),

            // More default styles
            Style::tag('h')->bold(),
            Style::tag('b')->bold(),
            Style::tag('em')->fgCyan(),
            Style::tag('tt')->underlined(),
            Style::tag('warn')->fgBlack()->bgYellow(),
        ));
    }
}
