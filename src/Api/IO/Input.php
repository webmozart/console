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
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Input
{
    /**
     * Reads the given amount of characters from the input.
     *
     * @param int $length The number of characters to read.
     *
     * @return string The characters read from the input.
     *
     * @throws IOException If reading fails or if the input is closed.
     */
    public function read($length);

    /**
     * Reads a line from the input.
     *
     * @param int $length The maximum number of characters to read. If `null`,
     *                    all characters up to the first newline are returned.
     *
     * @return string The characters read from the input.
     *
     * @throws IOException If reading fails or if the input is closed.
     */
    public function readLine($length = null);

    /**
     * Closes the input.
     */
    public function close();
}
