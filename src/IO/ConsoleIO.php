<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\IO;

use Symfony\Component\Console\Application;
use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Formatter\AnsiFormatter;
use Webmozart\Console\Formatter\PlainFormatter;
use Webmozart\Console\IO\Input\StandardInput;
use Webmozart\Console\IO\Output\ErrorOutput;
use Webmozart\Console\IO\Output\StandardOutput;
use Webmozart\Console\UI\Rectangle;

/**
 * An I/O that reads from/prints to the console.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ConsoleIO extends FormattedIO
{
    /**
     * Creates the I/O.
     *
     * @param Input     $input       The input.
     * @param Output    $output      The output.
     * @param Output    $errorOutput The error output.
     * @param Formatter $formatter   The formatter.
     * @param Rectangle $dimensions  The terminal dimensions.
     */
    public function __construct(Input $input = null, Output $output = null, Output $errorOutput = null, Formatter $formatter = null, Rectangle $dimensions = null)
    {
        $input = $input ?: new StandardInput();
        $output = $output ?: new StandardOutput();
        $errorOutput = $errorOutput ?: new ErrorOutput();
        $formatter = $formatter ?: ($output->supportsAnsi() ? new AnsiFormatter() : new PlainFormatter());

        parent::__construct($input, $output, $errorOutput, $formatter, $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTerminalDimensions()
    {
        $application = new Application();

        list ($width, $height) = $application->getTerminalDimensions();

        return new Rectangle($width ?: 80, $height ?: 20);
    }
}
