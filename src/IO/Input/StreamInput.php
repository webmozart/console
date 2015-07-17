<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\IO\Input;

use Webmozart\Assert\Assert;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IOException;

/**
 * An input that reads from a stream.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StreamInput implements Input
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * Creates the input.
     *
     * @param resource $stream A stream resource.
     */
    public function __construct($stream)
    {
        Assert::resource($stream, 'stream');

        $this->stream = $stream;

        // Not all streams are seekable
        @rewind($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (null === $this->stream) {
            throw new IOException('Cannot read from a closed input.');
        }

        if (feof($this->stream)) {
            return null;
        }

        $data = fread($this->stream, $length);

        if (false === $data && !feof($this->stream)) {
            throw new IOException('Could not read stream.');
        }

        return $data ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function readLine($length = null)
    {
        if (null === $this->stream) {
            throw new IOException('Cannot read from a closed input.');
        }

        if (feof($this->stream)) {
            return null;
        }

        if (null !== $length) {
            $data = fgets($this->stream, $length);
        } else {
            $data = fgets($this->stream);
        }

        if (false === $data && !feof($this->stream)) {
            throw new IOException('Could not read stream.');
        }

        return $data ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->stream) {
            @fclose($this->stream);
            $this->stream = null;
        }
    }
}
