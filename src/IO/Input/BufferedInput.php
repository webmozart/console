<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\IO\Input;

/**
 * An input that reads from a buffer.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BufferedInput extends StreamInput
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * Creates the input.
     *
     * @param string $data The data of the buffer.
     */
    public function __construct($data = '')
    {
        $this->stream = fopen('php://memory', 'rw');

        parent::__construct($this->stream);

        $this->set($data);
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
     * Fills the input with data.
     *
     * @param string $data The data of the buffer.
     */
    public function set($data)
    {
        $this->clear();

        fwrite($this->stream, $data);
        rewind($this->stream);
    }

    /**
     * Appends data to the buffer.
     *
     * @param string $data The data to append.
     */
    public function append($data)
    {
        $pos = ftell($this->stream);
        fseek($this->stream, 0, SEEK_END);
        fwrite($this->stream, $data);
        fseek($this->stream, $pos);
    }
}
