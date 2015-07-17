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

use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Formatter\AnsiFormatter;
use Webmozart\Console\Formatter\PlainFormatter;
use Webmozart\Console\UI\Rectangle;

/**
 * A formatted I/O.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormattedIO extends RawIO
{
    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var bool
     */
    private $formatOutput;

    /**
     * @var bool
     */
    private $formatErrors;

    /**
     * Creates the I/O.
     *
     * @param Input     $input       The input.
     * @param Output    $output      The output.
     * @param Output    $errorOutput The error output.
     * @param Formatter $formatter   The formatter.
     * @param Rectangle $dimensions  The terminal dimensions.
     */
    public function __construct(Input $input, Output $output, Output $errorOutput, Formatter $formatter = null, Rectangle $dimensions = null)
    {
        parent::__construct($input, $output, $errorOutput, $dimensions);

        $this->formatter = $formatter ?: new PlainFormatter();
        $this->formatOutput = $output->supportsAnsi() || !($this->formatter instanceof AnsiFormatter);
        $this->formatErrors = $errorOutput->supportsAnsi() || !($this->formatter instanceof AnsiFormatter);
    }

    /**
     * Returns the formatter.
     *
     * @return Formatter The formatter.
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $formatted = $this->formatOutput ? $this->format($string) : $this->removeFormat($string);
            $this->getOutput()->write($formatted);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeLine($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $string = rtrim($string, PHP_EOL);
            $formatted = $this->formatOutput ? $this->format($string) : $this->removeFormat($string);
            $this->getOutput()->write($formatted.PHP_EOL);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function error($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $formatted = $this->formatErrors ? $this->format($string) : $this->removeFormat($string);
            $this->getErrorOutput()->write($formatted);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function errorLine($string, $flags = null)
    {
        if ($this->mayWrite($flags)) {
            $string = rtrim($string, PHP_EOL);
            $formatted = $this->formatErrors ? $this->format($string) : $this->removeFormat($string);
            $this->getErrorOutput()->write($formatted.PHP_EOL);
        }
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
}
