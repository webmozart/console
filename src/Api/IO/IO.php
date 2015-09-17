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
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\UI\Rectangle;

/**
 * Provides methods to access the console input and output.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class IO implements Formatter
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
     * @var Input
     */
    private $input;

    /**
     * @var Output
     */
    private $output;

    /**
     * @var Output
     */
    private $errorOutput;

    /**
     * @var Rectangle
     */
    private $terminalDimensions;

    /**
     * Creates an I/O based on the given input and outputs.
     *
     * @param Input  $input       The standard input.
     * @param Output $output      The standard output.
     * @param Output $errorOutput The error output.
     */
    public function __construct(Input $input, Output $output, Output $errorOutput)
    {
        $this->input = $input;
        $this->output = $output;
        $this->errorOutput = $errorOutput;
    }

    /**
     * Returns the standard input.
     *
     * @return Input The input.
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Returns the standard output.
     *
     * @return Output The output.
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Returns the error output.
     *
     * @return Output The error output.
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }

    /**
     * Reads the given amount of characters from the standard input.
     *
     * @param int    $length  The number of characters to read.
     * @param string $default The default to return if interaction is disabled.
     *
     * @return string The characters read from the input.
     *
     * @throws IOException If reading fails or if the standard input is closed.
     */
    public function read($length, $default = null)
    {
        return $this->input->read($length, $default);
    }

    /**
     * Reads a line from the standard input.
     *
     * @param string $default The default to return if interaction is disabled.
     * @param int    $length  The maximum number of characters to read. If
     *                        `null`, all characters up to the first newline are
     *                        returned.
     *
     * @return string The characters read from the input.
     *
     * @throws IOException If reading fails or if the standard input is closed.
     */
    public function readLine($default = null, $length = null)
    {
        return $this->input->readLine($default, $length);
    }

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
    public function write($string, $flags = null)
    {
        $this->output->write($string, $flags);
    }

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
    public function writeLine($string, $flags = null)
    {
        $this->output->writeLine($string, $flags);
    }

    /**
     * Writes a string to the standard output without formatting.
     *
     * @param string $string The string to write.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the standard output is closed.
     */
    public function writeRaw($string, $flags = null)
    {
        $this->output->writeRaw($string, $flags);
    }

    /**
     * Writes a line of text to the standard output without formatting.
     *
     * @param string $string The string to write. A newline is appended.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the standard output is closed.
     */
    public function writeLineRaw($string, $flags = null)
    {
        $this->output->writeLineRaw($string, $flags);
    }

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
    public function error($string, $flags = null)
    {
        $this->errorOutput->write($string, $flags);
    }

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
    public function errorLine($string, $flags = null)
    {
        $this->errorOutput->writeLine($string, $flags);
    }

    /**
     * Writes a string to the error output without formatting.
     *
     * @param string $string The string to write.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the error output is closed.
     */
    public function errorRaw($string, $flags = null)
    {
        $this->errorOutput->writeRaw($string, $flags);
    }

    /**
     * Writes a line of text to the error output without formatting.
     *
     * @param string $string The string to write. A newline is appended.
     * @param int    $flags  The flags. One of {@link VERBOSE},
     *                       {@link VERY_VERBOSE} and {@link DEBUG}.
     *
     * @throws IOException If writing fails or if the error output is closed.
     */
    public function errorLineRaw($string, $flags = null)
    {
        $this->errorOutput->writeLineRaw($string, $flags);
    }

    /**
     * Flushes the outputs and forces all pending text to be written out.
     *
     * @throws IOException If flushing fails or if the outputs are closed.
     */
    public function flush()
    {
        $this->output->flush();
        $this->errorOutput->flush();
    }

    /**
     * Closes the input and the outputs.
     */
    public function close()
    {
        $this->input->close();
        $this->output->close();
        $this->errorOutput->close();
    }

    /**
     * Enables or disables interaction with the user.
     *
     * @param bool $interactive Whether the I/O may interact with the user. If
     *                          set to `false`, all calls to {@link read()} and
     *                          {@link readLine()} will immediately return the
     *                          default value.
     */
    public function setInteractive($interactive)
    {
        $this->input->setInteractive($interactive);
    }

    /**
     * Returns whether the user may be asked for input.
     *
     * @return bool Returns `true` if the user may be asked for input and
     *              `false` otherwise.
     */
    public function isInteractive()
    {
        return $this->input->isInteractive();
    }

    /**
     * Sets the verbosity of the output.
     *
     * @param int $verbosity One of the constants {@link NORMAL}, {@link VERBOSE},
     *                       {@link VERY_VERBOSE} or {@link DEBUG}. Only output
     *                       with the given verbosity level or smaller will be
     *                       passed through.
     */
    public function setVerbosity($verbosity)
    {
        $this->output->setVerbosity($verbosity);
        $this->errorOutput->setVerbosity($verbosity);
    }

    /**
     * Returns whether the verbosity is {@link VERBOSE} or greater.
     *
     * @return bool Returns `true` if the verbosity is {@link VERBOSE} or
     *              greater and `false` otherwise.
     */
    public function isVerbose()
    {
        return $this->output->isVerbose();
    }

    /**
     * Returns whether the verbosity is {@link VERY_VERBOSE} or greater.
     *
     * @return bool Returns `true` if the verbosity is {@link VERY_VERBOSE} or
     *              greater and `false` otherwise.
     */
    public function isVeryVerbose()
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * Returns whether the verbosity is {@link DEBUG}.
     *
     * @return bool Returns `true` if the verbosity is {@link DEBUG} and `false`
     *              otherwise.
     */
    public function isDebug()
    {
        return $this->output->isDebug();
    }

    /**
     * Returns the current verbosity level.
     *
     * @return int One of the verbosity constants.
     */
    public function getVerbosity()
    {
        return $this->output->getVerbosity();
    }

    /**
     * Sets whether all output should be suppressed.
     *
     * @param bool $quiet Pass `true` to suppress all output and `false`
     *                    otherwise.
     */
    public function setQuiet($quiet)
    {
        $this->output->setQuiet($quiet);
        $this->errorOutput->setQuiet($quiet);
    }

    /**
     * Returns whether all output is suppressed.
     *
     * @return bool Returns `true` if all output is suppressed and `false`
     *              otherwise.
     */
    public function isQuiet()
    {
        return $this->output->isQuiet();
    }

    /**
     * Sets the dimensions of the terminal.
     *
     * @param Rectangle $dimensions The terminal dimensions.
     */
    public function setTerminalDimensions(Rectangle $dimensions)
    {
        $this->terminalDimensions = $dimensions;
    }

    /**
     * Returns the dimensions of the terminal.
     *
     * @return Rectangle The terminal dimensions.
     */
    public function getTerminalDimensions()
    {
        if (!$this->terminalDimensions) {
            $this->terminalDimensions = $this->getDefaultTerminalDimensions();
        }

        return $this->terminalDimensions;
    }

    /**
     * Sets the output formatter.
     *
     * @param Formatter $formatter The output formatter.
     */
    public function setFormatter(Formatter $formatter)
    {
        $this->output->setFormatter($formatter);
        $this->errorOutput->setFormatter($formatter);
    }

    /**
     * Returns the output formatter.
     *
     * @return Formatter The output formatter.
     */
    public function getFormatter()
    {
        return $this->output->getFormatter();
    }

    /**
     * {@inheritdoc}
     */
    public function format($string, Style $style = null)
    {
        return $this->output->format($string, $style);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFormat($string)
    {
        return $this->output->removeFormat($string);
    }

    /**
     * Returns the default terminal dimensions.
     *
     * @return Rectangle The terminal dimensions.
     */
    protected function getDefaultTerminalDimensions()
    {
        return new Rectangle(80, 20);
    }
}
