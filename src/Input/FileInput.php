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
 * An input that reads from a file path.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FileInput implements Input
{
    /**
     * @var resource
     */
    private $handle;

    /**
     * Creates the input.
     *
     * @param string $path The file path.
     */
    public function __construct($path)
    {
        $this->handle = fopen($path, 'r');
    }

    /**
     * Releases the acquired file handle.
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
