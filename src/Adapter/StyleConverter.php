<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Adapter;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Webmozart\Console\Api\Formatter\Style;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StyleConverter
{
    public static function convert(Style $style)
    {
        $options = array();

        if ($style->isBold()) {
            $options[] = 'bold';
        }

        if ($style->isBlinking()) {
            $options[] = 'blink';
        }

        if ($style->isUnderlined()) {
            $options[] = 'underscore';
        }

        if ($style->isReversed()) {
            $options[] = 'reverse';
        }

        if ($style->isConcealed()) {
            $options[] = 'conceal';
        }

        return new OutputFormatterStyle($style->getForegroundColor(), $style->getBackgroundColor(), $options);
    }

    private function __construct()
    {
    }
}
