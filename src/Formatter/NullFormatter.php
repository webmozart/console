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

use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Api\Formatter\Style;

/**
 * A formatter that returns all text unchanged.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format($string, Style $style = null)
    {
        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFormat($string)
    {
        return $string;
    }
}
