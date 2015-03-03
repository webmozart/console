<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\UI;

/**
 * A rectangle.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Rectangle
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * Creates dimensions with the given width and height.
     *
     * @param int $width  The width as number of printable characters.
     * @param int $height The height as number of printable lines.
     */
    public function __construct($width, $height)
    {
        $this->width = (int) $width;
        $this->height = (int) $height;
    }

    /**
     * Returns the width.
     *
     * @return int The width as number of printable characters.
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Returns the height.
     *
     * @return int The height as number of printable lines.
     */
    public function getHeight()
    {
        return $this->height;
    }
}
