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
use Webmozart\Console\Api\IOException;
use Webmozart\Console\Assert\Assert;

/**
 * An input that reads from a stream.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StreamInput implements Input
{
    /**
     * @var resource
     */
    private $handle;

    /**
     * Creates the input.
     *
     * @param resource $handle A stream resource.
     */
    public function __construct($handle)
    {
        Assert::stream($handle);

        $this->handle = $handle;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length = 1)
    {
        if (null === $this->handle) {
            throw new IOException('Cannot read from a closed input.');
        }

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
        if (null === $this->handle) {
            throw new IOException('Cannot read from a closed input.');
        }

        if (feof($this->handle)) {
            return null;
        }

        if (null !== $length) {
            return fgets($this->handle, $length);
        }

        return fgets($this->handle);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->handle) {
            @fclose($this->handle);
            $this->handle = null;
        }
    }
}
