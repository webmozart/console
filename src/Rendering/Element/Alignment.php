<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering\Element;

/**
 * Constants for text alignment.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class Alignment
{
    /**
     * Alignment: Align a cell to the left.
     */
    const LEFT = 0;

    /**
     * Alignment: Align a cell to the right.
     */
    const RIGHT = 1;

    /**
     * Alignment: Align a cell to the center.
     */
    const CENTER = 2;

    /**
     * Returns all possible alignments.
     *
     * @return int[] A list of valid alignment constants.
     */
    public static function all()
    {
        return array(
            self::LEFT,
            self::RIGHT,
            self::CENTER,
        );
    }

    private function __construct()
    {
    }
}
