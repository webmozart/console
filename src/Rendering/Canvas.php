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

use Webmozart\Console\Api\IO\IO;

/**
 * A canvas that can be written on.
 *
 * Contrary to a plain {@link IO} instance, a canvas has fixed dimensions. You
 * can use these dimensions to wrap and nicely align objects on the canvas
 * before drawing the canvas.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Canvas
{
    /**
     * @var IO
     */
    private $io;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var string
     */
    private $buffer;

    /**
     * @var bool
     */
    private $flushOnWrite = false;

    public function __construct(IO $io, Dimensions $dimensions = null)
    {
        if (!$dimensions) {
            $dimensions = Dimensions::forCurrentWindow();
        }

        $this->io = $io;
        $this->width = $dimensions->getWidth();
        $this->height = $dimensions->getHeight();
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getDimensions()
    {
        return new Dimensions($this->width, $this->height);
    }

    public function getIO()
    {
        return $this->io;
    }

    /**
     * @return boolean
     */
    public function isFlushedOnWrite()
    {
        return $this->flushOnWrite;
    }

    public function setFlushOnWrite($flushOnWrite)
    {
        $this->flushOnWrite = $flushOnWrite;
    }

    public function write($string)
    {
        $this->buffer .= $string;

        if ($this->flushOnWrite) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->io->write($this->buffer);
        $this->buffer = '';
    }
}
