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

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Webmozart\Console\Api\Style\StyleSet;

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
        $this->setStyles(array(
            // Symfony default styles
            'error' => new OutputFormatterStyle('white', 'red'),
            'info' => new OutputFormatterStyle('green'),
            'comment' => new OutputFormatterStyle('yellow'),
            'question' => new OutputFormatterStyle('black', 'cyan'),

            // More default styles
            'h' => new OutputFormatterStyle(null, null, array('bold')),
            'b' => new OutputFormatterStyle(null, null, array('bold')),
            'em' => new OutputFormatterStyle('cyan'),
            'tt' => new OutputFormatterStyle(null, null, array('underscore')),
            'warn' => new OutputFormatterStyle('black', 'yellow'),
        ));
    }
}
