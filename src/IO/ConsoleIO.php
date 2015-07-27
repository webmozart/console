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
use Webmozart\Console\Api\IO\InputStream;
use Webmozart\Console\Api\IO\OutputStream;
use Webmozart\Console\Formatter\AnsiFormatter;
use Webmozart\Console\Formatter\PlainFormatter;
use Webmozart\Console\IO\InputStream\StandardInputStream;
use Webmozart\Console\IO\OutputStream\ErrorOutputStream;
use Webmozart\Console\IO\OutputStream\StandardOutputStream;
use Webmozart\Console\UI\Rectangle;

/**
 * An I/O that reads from/prints to the console.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ConsoleIO extends FormattedIO
{
    /**
     * Creates the I/O.
     *
     * @param InputStream     $input       The input.
     * @param OutputStream    $output      The output.
     * @param OutputStream    $errorOutput The error output.
     * @param Formatter $formatter   The formatter.
     * @param Rectangle $dimensions  The terminal dimensions.
     */
    public function __construct(InputStream $input = null, OutputStream $output = null, OutputStream $errorOutput = null, Formatter $formatter = null, Rectangle $dimensions = null)
    {
        $input = $input ?: new StandardInputStream();
        $output = $output ?: new StandardOutputStream();
        $errorOutput = $errorOutput ?: new ErrorOutputStream();
        $formatter = $formatter ?: ($output->supportsAnsi() ? new AnsiFormatter() : new PlainFormatter());

        parent::__construct($input, $output, $errorOutput, $formatter, $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTerminalDimensions()
    {
        $application = new Application();

        list($width, $height) = $application->getTerminalDimensions();

        return new Rectangle($width ?: 80, $height ?: 20);
    }
}
