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
use Webmozart\Console\IO\Input\BufferedInput;
use Webmozart\Console\IO\Output\BufferedOutput;
use Webmozart\Console\Rendering\Rectangle;

/**
 * An I/O that reads from and writes to a buffer.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BufferedIO extends FormattedIO
{
    /**
     * @var BufferedInput
     */
    private $input;

    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var BufferedOutput
     */
    private $errorOutput;

    /**
     * Creates the I/O.
     *
     * @param string    $inputData  The data to return from the input.
     * @param Formatter $formatter  The formatter to use.
     * @param Rectangle $dimensions The terminal dimensions.
     */
    public function __construct($inputData = '', Formatter $formatter = null, Rectangle $dimensions = null)
    {
        $this->input = new BufferedInput($inputData);
        $this->output = new BufferedOutput();
        $this->errorOutput = new BufferedOutput();

        parent::__construct($this->input, $this->output, $this->errorOutput, $formatter);
    }

    /**
     * Sets the contents of the input buffer.
     *
     * @param string $data The input data.
     */
    public function setInput($data)
    {
        $this->input->set($data);
    }

    /**
     * Appends data to the input buffer.
     *
     * @param string $data The input data to append.
     */
    public function appendInput($data)
    {
        $this->input->append($data);
    }

    /**
     * Clears the input buffer.
     */
    public function clearInput()
    {
        $this->input->clear();
    }

    /**
     * Returns the contents of the output buffer.
     *
     * @return string The output data.
     */
    public function fetchOutput()
    {
        return $this->output->fetch();
    }

    /**
     * Clears the output buffer.
     */
    public function clearOutput()
    {
        $this->output->clear();
    }

    /**
     * Returns the contents of the error output buffer.
     *
     * @return string The data of the error output.
     */
    public function fetchErrors()
    {
        return $this->errorOutput->fetch();
    }

    /**
     * Clears the error output buffer.
     */
    public function clearErrors()
    {
        $this->errorOutput->clear();
    }
}
