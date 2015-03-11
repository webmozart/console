<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\IO;

use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\UI\Rectangle;

/**
 * An unformatted I/O.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RawIO implements IO
{
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
     * @var bool
     */
    private $interactive = true;

    /**
     * @var bool
     */
    private $quiet = false;

    /**
     * @var int
     */
    private $verbosity = 0;

    /**
     * @var Rectangle
     */
    private $terminalDimensions;

    /**
     * Creates the I/O.
     *
     * @param Input     $input       The input.
     * @param Output    $output      The output.
     * @param Output    $errorOutput The error output.
     * @param Rectangle $dimensions  The dimensions of the terminal.
     */
    public function __construct(Input $input, Output $output, Output $errorOutput, Rectangle $dimensions = null)
    {
        $this->input = $input;
        $this->output = $output;
        $this->errorOutput = $errorOutput;
        $this->terminalDimensions = $dimensions;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length, $default = null)
    {
        if (!$this->interactive) {
            return $default;
        }

        return $this->input->read($length);
    }

    /**
     * {@inheritdoc}
     */
    public function readLine($default = null, $length = null)
    {
        if (!$this->interactive) {
            return $default;
        }

        return $this->input->readLine($length);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string, $flags = null)
    {
        $this->writeRaw($string, $flags);
    }

    /**
     * {@inheritdoc}
     */
    public function writeLine($string, $flags = null)
    {
        $this->writeLineRaw($string, $flags);
    }

    /**
     * {@inheritdoc}
     */
    public function writeRaw($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $this->output->write($string);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeLineRaw($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $this->output->write(rtrim($string, PHP_EOL).PHP_EOL);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function error($string, $flags = null)
    {
        $this->errorRaw($string, $flags);
    }

    /**
     * {@inheritdoc}
     */
    public function errorLine($string, $flags = null)
    {
        $this->errorLineRaw($string, $flags);
    }

    /**
     * {@inheritdoc}
     */
    public function errorRaw($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $this->errorOutput->write($string);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function errorLineRaw($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $this->errorOutput->write(rtrim($string, PHP_EOL).PHP_EOL);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format($string, Style $style = null)
    {
        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFormat($string)
    {
        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->output->flush();
        $this->errorOutput->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->input->close();
        $this->output->close();
        $this->errorOutput->close();
    }

    /**
     * Returns the underlying input.
     *
     * @return Input The input.
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Returns the underlying output.
     *
     * @return Output The output.
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Returns the underlying error output.
     *
     * @return Output The error output.
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
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
        $this->interactive = (bool) $interactive;
    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive()
    {
        return $this->interactive;
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
        Assert::oneOf($verbosity, array(self::NORMAL, self::VERBOSE, self::VERY_VERBOSE, self::DEBUG), 'The verbosity must be one of IO::NORMAL, IO::VERBOSE, IO::VERY_VERBOSE and IO::DEBUG.');

        $this->verbosity = (int) $verbosity;
    }

    /**
     * {@inheritdoc}
     */
    public function getVerbosity()
    {
        return $this->verbosity;
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose()
    {
        return $this->verbosity >= self::VERBOSE;
    }

    /**
     * {@inheritdoc}
     */
    public function isVeryVerbose()
    {
        return $this->verbosity >= self::VERY_VERBOSE;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug()
    {
        return self::DEBUG === $this->verbosity;
    }

    /**
     * Sets whether all output should be suppressed.
     *
     * @param bool $quiet Pass `true` to suppress all output and `false`
     *                    otherwise.
     */
    public function setQuiet($quiet)
    {
        $this->quiet = (bool) $quiet;
    }

    /**
     * {@inheritdoc}
     */
    public function isQuiet()
    {
        return $this->quiet;
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
     * {@inheritdoc}
     */
    public function getTerminalDimensions()
    {
        if (!$this->terminalDimensions) {
            $this->terminalDimensions = $this->getDefaultTerminalDimensions();
        }

        return $this->terminalDimensions;
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

        if ($flags & self::VERBOSE) {
            return $this->verbosity >= self::VERBOSE;
        }

        if ($flags & self::VERY_VERBOSE) {
            return $this->verbosity >= self::VERY_VERBOSE;
        }

        if ($flags & self::DEBUG) {
            return $this->verbosity >= self::DEBUG;
        }

        return true;
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
