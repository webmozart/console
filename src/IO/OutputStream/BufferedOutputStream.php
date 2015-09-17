<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\IO\OutputStream;

use Webmozart\Console\Api\IO\IOException;
use Webmozart\Console\Api\IO\OutputStream;

/**
 * An output stream that writes to a buffer.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BufferedOutputStream implements OutputStream
{
    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @var bool
     */
    private $closed = false;

    /**
     * Returns the contents of the buffer.
     *
     * @return string The buffered data.
     */
    public function fetch()
    {
        return $this->buffer;
    }

    /**
     * Clears the buffer.
     */
    public function clear()
    {
        $this->buffer = '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if ($this->closed) {
            throw new IOException('Cannot read from a closed input.');
        }

        $this->buffer .= $string;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if ($this->closed) {
            throw new IOException('Cannot read from a closed input.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAnsi()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed()
    {
        return $this->closed;
    }
}
