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

use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Rendering\Rectangle;

/**
 * Provides methods to access the console input and output.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface IO extends Formatter
{
    /**
     * Flag: Always write data.
     */
    const NORMAL = 0;

    /**
     * Flag: Only write if the verbosity is "verbose" or greater.
     */
    const VERBOSE = 1;

    /**
     * Flag: Only write if the verbosity is "very verbose" or greater.
     */
    const VERY_VERBOSE = 2;

    /**
     * Flag: Only write if the verbosity is "debug".
     */
    const DEBUG = 4;

    /**
     * Reads the given amount of characters from the input.
     *
     * @param int    $length  The number of characters to read.
     * @param string $default The default to return if interaction is disabled.
     *
     * @return string The characters read from the input.
     *
     * @throws IOException If reading fails or if the input is closed.
     */
    public function read($length, $default = null);

    /**
     * Reads a line from the input.
     *
     * @param string $default The default to return if interaction is disabled.
     * @param int    $length  The maximum number of characters to read. If
     *                        `null`, all characters up to the first newline are
     *                        returned.
     *
     * @return string The characters read from the input.
     *
     * @throws IOException If reading fails or if the input is closed.
     */
    public function readLine($default = null, $length = null);

    /**
     * Writes a string to the standard output.
     *
     * The string is formatted before it is written to the output.
     *
     * @param string $string The string to write.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the standard output is closed.
     */
    public function write($string, $flags = null);

    /**
     * Writes a line of text to the standard output.
     *
     * The string is formatted before it is written to the output.
     *
     * @param string $string The string to write. A newline is appended.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the standard output is closed.
     */
    public function writeLine($string, $flags = null);

    /**
     * Writes a string to the standard output without formatting.
     *
     * @param string $string The string to write.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the standard output is closed.
     */
    public function writeRaw($string, $flags = null);

    /**
     * Writes a line of text to the standard output without formatting.
     *
     * @param string $string The string to write. A newline is appended.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the standard output is closed.
     */
    public function writeLineRaw($string, $flags = null);

    /**
     * Writes a string to the error output.
     *
     * The string is formatted before it is written to the output.
     *
     * @param string $string The string to write.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the error output is closed.
     */
    public function error($string, $flags = null);

    /**
     * Writes a line of text to the error output.
     *
     * The string is formatted before it is written to the output.
     *
     * @param string $string The string to write. A newline is appended.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the error output is closed.
     */
    public function errorLine($string, $flags = null);

    /**
     * Writes a string to the error output without formatting.
     *
     * @param string $string The string to write.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the error output is closed.
     */
    public function errorRaw($string, $flags = null);

    /**
     * Writes a line of text to the error output without formatting.
     *
     * @param string $string The string to write. A newline is appended.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the error output is closed.
     */
    public function errorLineRaw($string, $flags = null);

    /**
     * Flushes the outputs and forces all pending text to be written out.
     *
     * @throws IOException If flushing fails or if the outputs are closed.
     */
    public function flush();

    /**
     * Closes the input and the outputs.
     */
    public function close();

    /**
     * Returns whether the user may be asked for input.
     *
     * @return bool Returns `true` if the user may be asked for input and
     *              `false` otherwise.
     */
    public function isInteractive();

    /**
     * Returns whether the verbosity is {@link VERBOSE} or greater.
     *
     * @return bool Returns `true` if the verbosity is {@link VERBOSE} or
     *              greater and `false` otherwise.
     */
    public function isVerbose();

    /**
     * Returns whether the verbosity is {@link VERY_VERBOSE} or greater.
     *
     * @return bool Returns `true` if the verbosity is {@link VERY_VERBOSE} or
     *              greater and `false` otherwise.
     */
    public function isVeryVerbose();

    /**
     * Returns whether the verbosity is {@link DEBUG}.
     *
     * @return bool Returns `true` if the verbosity is {@link DEBUG} and `false`
     *              otherwise.
     */
    public function isDebug();

    /**
     * Returns the current verbosity level.
     *
     * @return int One of the verbosity constants.
     */
    public function getVerbosity();

    /**
     * Returns whether all output is suppressed.
     *
     * @return bool Returns `true` if all output is suppressed and `false`
     *              otherwise.
     */
    public function isQuiet();

    /**
     * Returns the dimensions of the terminal.
     *
     * @return Rectangle The terminal dimensions.
     */
    public function getTerminalDimensions();
}
