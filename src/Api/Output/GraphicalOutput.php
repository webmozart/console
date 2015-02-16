<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Output;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface GraphicalOutput extends Output
{
    public function setCursor($x, $y);

    public function moveCursor($dx, $dy);

    public function getCursorX();

    public function getCursorY();

    /**
     * Returns the dimensions (width and height) of the output.
     *
     * @return Dimensions|null The output dimensions or `null` if the output
     *                         has no dimensions.
     */
    public function getDimensions();
}
