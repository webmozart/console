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
 * The console input stream.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface InputStream
{
    /**
     * Reads the given amount of characters from the stream.
     *
     * @param int $length The number of characters to read.
     *
     * @return string The characters read from the stream.
     *
     * @throws IOException If reading fails or if the stream is closed.
     */
    public function read($length);

    /**
     * Reads a line from the stream.
     *
     * @param int $length The maximum number of characters to read. If `null`,
     *                    all characters up to the first newline are returned.
     *
     * @return string The characters read from the stream.
     *
     * @throws IOException If reading fails or if the stream is closed.
     */
    public function readLine($length = null);

    /**
     * Closes the stream.
     */
    public function close();
}
