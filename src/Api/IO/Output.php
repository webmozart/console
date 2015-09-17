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

use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Formatter\AnsiFormatter;
use Webmozart\Console\Formatter\NullFormatter;

/**
 * The console output.
 *
 * This class wraps an output stream and adds convenience functionality for
 * writing that stream.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Output implements Formatter
{
    /**
     * @var OutputStream
     */
    private $stream;

    /**
     * @var bool
     */
    private $quiet = false;

    /**
     * @var int
     */
    private $verbosity = 0;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var bool
     */
    private $formatOutput;

    /**
     * Creates an output for the given output stream.
     *
     * @param OutputStream   $stream    The output stream.
     * @param Formatter|null $formatter The formatter for formatting text
     *                                  written to the output stream.
     */
    public function __construct(OutputStream $stream, Formatter $formatter = null)
    {
        $this->stream = $stream;

        $this->setFormatter($formatter ?: new NullFormatter());
    }

    /**
     * Writes a string to the output stream.
     *
     * The string is formatted before it is written to the output stream.
     *
     * @param string $string The string to write.
     * @param int    $flags  The flags. If one of of {@link IO::VERBOSE},
     *                       {@link IO::VERY_VERBOSE} and {@link IO::DEBUG} is
     *                       passed, the output is only written if the
     *                       verbosity level is the given level or higher.
     *
     * @throws IOException If writing fails or if the output stream is closed.
     */
    public function write($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $formatted = $this->formatOutput ? $this->format($string) : $this->removeFormat($string);
            $this->stream->write($formatted);
        }
    }

    /**
     * Writes a line of text to the output stream.
     *
     * The string is formatted before it is written to the output stream.
     *
     * @param string $string The string to write. A newline is appended.
     * @param int    $flags  The flags. If one of of {@link IO::VERBOSE},
     *                       {@link IO::VERY_VERBOSE} and {@link IO::DEBUG} is
     *                       passed, the output is only written if the
     *                       verbosity level is the given level or higher.
     *
     * @throws IOException If writing fails or if the output stream is closed.
     */
    public function writeLine($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $string = rtrim($string, PHP_EOL);
            $formatted = $this->formatOutput ? $this->format($string) : $this->removeFormat($string);
            $this->stream->write($formatted.PHP_EOL);
        }
    }

    /**
     * Writes a string to the output stream without formatting.
     *
     * @param string $string The string to write.
     * @param int    $flags  The flags. If one of of {@link IO::VERBOSE},
     *                       {@link IO::VERY_VERBOSE} and {@link IO::DEBUG} is
     *                       passed, the output is only written if the
     *                       verbosity level is the given level or higher.
     *
     * @throws IOException If writing fails or if the output stream is closed.
     */
    public function writeRaw($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $this->stream->write($string);
        }
    }

    /**
     * Writes a line of text to the output stream without formatting.
     *
     * @param string $string The string to write. A newline is appended.
     * @param int    $flags  The flags. If one of of {@link IO::VERBOSE},
     *                       {@link IO::VERY_VERBOSE} and {@link IO::DEBUG} is
     *                       passed, the output is only written if the
     *                       verbosity level is the given level or higher.
     *
     * @throws IOException If writing fails or if the standard output is closed.
     */
    public function writeLineRaw($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $this->stream->write(rtrim($string, PHP_EOL).PHP_EOL);
        }
    }

    /**
     * Forces all pending text to be written out.
     *
     * @throws IOException If flushing fails or if the output stream is closed.
     */
    public function flush()
    {
        $this->stream->flush();
    }

    /**
     * Closes the output.
     */
    public function close()
    {
        $this->stream->close();
    }

    /**
     * Returns whether the output is closed.
     *
     * @return bool Returns `true` if the output is closed and `false`
     *              otherwise.
     */
    public function isClosed()
    {
        return $this->stream->isClosed();
    }

    /**
     * Sets the underlying stream.
     *
     * @param OutputStream $stream The output stream.
     */
    public function setStream(OutputStream $stream)
    {
        $this->stream = $stream;
        $this->formatOutput = $stream->supportsAnsi() || !($this->formatter instanceof AnsiFormatter);
    }

    /**
     * Returns the underlying stream.
     *
     * @return OutputStream The output stream.
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Sets the output formatter.
     *
     * @param Formatter $formatter The output formatter.
     */
    public function setFormatter(Formatter $formatter)
    {
        $this->formatter = $formatter;
        $this->formatOutput = $this->stream->supportsAnsi() || !($formatter instanceof AnsiFormatter);
    }

    /**
     * Returns the output formatter.
     *
     * @return Formatter The output formatter.
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Sets the verbosity level of the output.
     *
     * @param int $verbosity One of the constants {@link NORMAL}, {@link VERBOSE},
     *                       {@link VERY_VERBOSE} or {@link DEBUG}. Only output
     *                       with the given verbosity level or smaller will be
     *                       written out.
     */
    public function setVerbosity($verbosity)
    {
        Assert::oneOf($verbosity, array(IO::NORMAL, IO::VERBOSE, IO::VERY_VERBOSE, IO::DEBUG), 'The verbosity must be one of IO::NORMAL, IO::VERBOSE, IO::VERY_VERBOSE and IO::DEBUG.');

        $this->verbosity = (int) $verbosity;
    }

    /**
     * Returns the current verbosity level.
     *
     * @return int One of the verbosity constants.
     */
    public function getVerbosity()
    {
        return $this->verbosity;
    }

    /**
     * Returns whether the verbosity level is {@link IO::VERBOSE} or greater.
     *
     * @return bool Returns `true` if the verbosity level is {@link IO::VERBOSE}
     *              or greater and `false` otherwise.
     */
    public function isVerbose()
    {
        return $this->verbosity >= IO::VERBOSE;
    }

    /**
     * Returns whether the verbosity level is {@link IO::VERY_VERBOSE} or greater.
     *
     * @return bool Returns `true` if the verbosity level is
     *              {@link IO::VERY_VERBOSE} or greater and `false` otherwise.
     */
    public function isVeryVerbose()
    {
        return $this->verbosity >= IO::VERY_VERBOSE;
    }

    /**
     * Returns whether the verbosity level is {@link IO::DEBUG}.
     *
     * @return bool Returns `true` if the verbosity level is {@link IO::DEBUG}
     *              and `false` otherwise.
     */
    public function isDebug()
    {
        return IO::DEBUG === $this->verbosity;
    }

    /**
     * Sets whether output should be suppressed completely.
     *
     * @param bool $quiet Pass `true` to suppress all output and `false`
     *                    otherwise.
     */
    public function setQuiet($quiet)
    {
        $this->quiet = (bool) $quiet;
    }

    /**
     * Returns whether output is suppressed completely.
     *
     * @return bool Returns `true` if all output is suppressed and `false`
     *              otherwise.
     */
    public function isQuiet()
    {
        return $this->quiet;
    }

    /**
     * {@inheritdoc}
     */
    public function format($string, Style $style = null)
    {
        return $this->formatter->format($string, $style);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFormat($string)
    {
        return $this->formatter->removeFormat($string);
    }

    /**
     * Returns whether an output may be written for the given flags.
     *
     * @param int $flags The flags.
     *
     * @return bool Returns `true` if the output may be written and `false`
     *              otherwise.
     */
    protected function mayWrite($flags)
    {
        if ($this->quiet) {
            return false;
        }

        if ($flags & IO::VERBOSE) {
            return $this->verbosity >= IO::VERBOSE;
        }

        if ($flags & IO::VERY_VERBOSE) {
            return $this->verbosity >= IO::VERY_VERBOSE;
        }

        if ($flags & IO::DEBUG) {
            return $this->verbosity >= IO::DEBUG;
        }

        return true;
    }
}
