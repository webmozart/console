<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Input;

use Webmozart\Console\Api\Input\Input;

/**
 * An input that reads from a string.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StringInput implements Input
{
    /**
     * @var resource
     */
    private $handle;

    /**
     * Creates the input.
     *
     * @param string $string The input string.
     */
    public function __construct($string)
    {
        $this->handle = fopen('php://memory', 'rw');

        fwrite($this->handle, $string);
        rewind($this->handle);
    }

    /**
     * Releases the acquired memory.
     */
    public function __destruct()
    {
        fclose($this->handle);
    }

    /**
     * {@inheritdoc}
     */
    public function read($length = 1)
    {
        if (feof($this->handle)) {
            return null;
        }

        return fread($this->handle, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function readLine($length = null)
    {
        if (feof($this->handle)) {
            return null;
        }

        if (null !== $length) {
            return fgets($this->handle, $length);
        }

        return fgets($this->handle);
    }
}
