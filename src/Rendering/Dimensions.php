<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering;

use Symfony\Component\Console\Application;

/**
 * A (width, height) tuple.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Dimensions
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
     * Returns the dimensions of the current terminal window.
     *
     * @return Dimensions The dimensions.
     */
    public static function forCurrentWindow()
    {
        $application = new Application();

        list ($width, $height) = $application->getTerminalDimensions();

        return new static($width ?: 80, $height ?: 20);
    }

    /**
     * Creates dimensions with the given width and height.
     *
     * @param int $width  The width as number of printable characters.
     * @param int $height The height as number of printable lines.
     */
    public function __construct($width, $height)
    {
        // HHVM only accepts 32 bits integer in str_split, even when PHP_INT_MAX
        // is a 64 bit integer.
        // https://github.com/facebook/hhvm/issues/1327
        if (defined('HHVM_VERSION') && $width > 1 << 31) {
            $width = 1 << 31;
        }

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
