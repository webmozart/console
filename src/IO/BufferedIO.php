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

use Webmozart\Console\Api\Formatter\StyleSet;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Formatter\PlainFormatter;
use Webmozart\Console\IO\InputStream\StringInputStream;
use Webmozart\Console\IO\OutputStream\BufferedOutputStream;

/**
 * An I/O that reads from and writes to a buffer.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BufferedIO extends IO
{
    /**
     * Creates the I/O.
     *
     * @param string   $inputData The data to return from the input.
     * @param StyleSet $styleSet  The style set to use.
     */
    public function __construct($inputData = '', StyleSet $styleSet = null)
    {
        $formatter = new PlainFormatter($styleSet);
        $input = new Input(new StringInputStream($inputData));
        $output = new Output(new BufferedOutputStream(), $formatter);
        $errorOutput = new Output(new BufferedOutputStream(), $formatter);

        parent::__construct($input, $output, $errorOutput);
    }

    /**
     * Sets the contents of the input buffer.
     *
     * @param string $data The input data.
     */
    public function setInput($data)
    {
        $this->getInput()->getStream()->set($data);
    }

    /**
     * Appends data to the input buffer.
     *
     * @param string $data The input data to append.
     */
    public function appendInput($data)
    {
        $this->getInput()->getStream()->append($data);
    }

    /**
     * Clears the input buffer.
     */
    public function clearInput()
    {
        $this->getInput()->getStream()->clear();
    }

    /**
     * Returns the contents of the output buffer.
     *
     * @return string The output data.
     */
    public function fetchOutput()
    {
        return $this->getOutput()->getStream()->fetch();
    }

    /**
     * Clears the output buffer.
     */
    public function clearOutput()
    {
        $this->getOutput()->getStream()->clear();
    }

    /**
     * Returns the contents of the error output buffer.
     *
     * @return string The data of the error output.
     */
    public function fetchErrors()
    {
        return $this->getErrorOutput()->getStream()->fetch();
    }

    /**
     * Clears the error output buffer.
     */
    public function clearErrors()
    {
        $this->getErrorOutput()->getStream()->clear();
    }
}
