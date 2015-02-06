<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api;

use Symfony\Component\Console\Application;

/**
 * The dimensions of the terminal window.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TerminalDimensions
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
     * Creates a terminal window for the current window.
     *
     * @return TerminalDimensions The terminal window.
     */
    public static function forCurrentWindow()
    {
        $application = new Application();

        list ($width, $height) = $application->getTerminalDimensions();

        return new static($width ?: 80, $height ?: 20);
    }

    /**
     * Creates a terminal window with the given width and height.
     *
     * @param int $width  The terminal width.
     * @param int $height The terminal height.
     */
    public function __construct($width, $height)
    {
        $this->width = (int) $width;
        $this->height = (int) $height;
    }

    /**
     * Returns the width of the terminal window.
     *
     * @return int The width of the terminal window in the number of printable
     *             characters.
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Returns the height of the terminal window.
     *
     * @return int The height of the terminal window in the number of printable
     *             lines.
     */
    public function getHeight()
    {
        return $this->height;
    }
}
