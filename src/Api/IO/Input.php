<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\IO;

/**
 * The console input.
 *
 * This class wraps an input stream and adds convenience functionality for
 * reading that stream.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Input
{
    /**
     * @var InputStream
     */
    private $stream;

    /**
     * @var bool
     */
    private $interactive = true;

    /**
     * Creates an input for the given input stream.
     *
     * @param InputStream $stream The input stream.
     */
    public function __construct(InputStream $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Reads the given amount of characters from the input stream.
     *
     * @param int    $length  The number of characters to read.
     * @param string $default The default to return if interaction is disabled.
     *
     * @return string The characters read from the input stream.
     *
     * @throws IOException If reading fails or if the input stream is closed.
     */
    public function read($length, $default = null)
    {
        if (!$this->interactive) {
            return $default;
        }

        return $this->stream->read($length);
    }

    /**
     * Reads a line from the input stream.
     *
     * @param string $default The default to return if interaction is disabled.
     * @param int    $length  The maximum number of characters to read. If
     *                        `null`, all characters up to the first newline are
     *                        returned.
     *
     * @return string The characters read from the input stream.
     *
     * @throws IOException If reading fails or if the input stream is closed.
     */
    public function readLine($default = null, $length = null)
    {
        if (!$this->interactive) {
            return $default;
        }

        return $this->stream->readLine($length);
    }

    /**
     * Closes the input.
     */
    public function close()
    {
        $this->stream->close();
    }

    /**
     * Returns whether the input is closed.
     *
     * @return bool Returns `true` if the input is closed and `false`
     *              otherwise.
     */
    public function isClosed()
    {
        return $this->stream->isClosed();
    }

    /**
     * Sets the underlying stream.
     *
     * @param InputStream $stream The input stream.
     */
    public function setStream(InputStream $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Returns the underlying stream.
     *
     * @return InputStream The input stream.
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Enables or disables interaction with the user.
     *
     * @param bool $interactive Whether the inputmay interact with the user. If
     *                          set to `false`, all calls to {@link read()} and
     *                          {@link readLine()} will immediately return the
     *                          default value.
     */
    public function setInteractive($interactive)
    {
        $this->interactive = (bool) $interactive;
    }

    /**
     * Returns whether the user may be asked for input.
     *
     * @return bool Returns `true` if the user may be asked for input and
     *              `false` otherwise.
     */
    public function isInteractive()
    {
        return $this->interactive;
    }
}
