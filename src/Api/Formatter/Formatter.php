<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Formatter;

/**
 * Formats strings.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Formatter
{
    /**
     * Formats the given string.
     *
     * @param string $string The string to format.
     * @param Style  $style  The style to use.
     *
     * @return string The formatted string.
     */
    public function format($string, Style $style = null);

    /**
     * Removes the format tags from the given string.
     *
     * @param string $string The string to remove the format tags from.
     *
     * @return string The string without format tags.
     */
    public function removeFormat($string);
}
