<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\IO\InputStream;

/**
 * An input stream that reads from a string.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StringInputStream extends StreamInputStream
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * Creates the input stream.
     *
     * @param string $string The string to read from.
     */
    public function __construct($string = '')
    {
        $this->stream = fopen('php://memory', 'rw');

        parent::__construct($this->stream);

        $this->set($string);
    }

    /**
     * Clears the contents of the buffer.
     */
    public function clear()
    {
        ftruncate($this->stream, 0);
        rewind($this->stream);
    }

    /**
     * Sets the string to read from.
     *
     * @param string $string The string to read from.
     */
    public function set($string)
    {
        $this->clear();

        fwrite($this->stream, $string);
        rewind($this->stream);
    }

    /**
     * Appends a string to the stream.
     *
     * @param string $string The string to append.
     */
    public function append($string)
    {
        $pos = ftell($this->stream);
        fseek($this->stream, 0, SEEK_END);
        fwrite($this->stream, $string);
        fseek($this->stream, $pos);
    }
}
