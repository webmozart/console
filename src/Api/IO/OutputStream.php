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
 * The console output stream.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface OutputStream
{
    /**
     * Writes a string to the stream.
     *
     * @param string $string The string to write.
     *
     * @throws IOException If writing fails or if the stream is closed.
     */
    public function write($string);

    /**
     * Flushes the stream and forces all pending text to be written out.
     *
     * @throws IOException If flushing fails or if the stream is closed.
     */
    public function flush();

    /**
     * Returns whether the stream supports ANSI format codes.
     *
     * @return bool Returns `true` if the stream supports ANSI format codes and
     *              `false` otherwise.
     */
    public function supportsAnsi();

    /**
     * Closes the stream.
     */
    public function close();
}
