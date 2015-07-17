<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\IO\Output;

use Webmozart\Assert\Assert;
use Webmozart\Console\Api\IO\IOException;
use Webmozart\Console\Api\IO\Output;

/**
 * An output that writes to a stream.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StreamOutput implements Output
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * Creates the output.
     *
     * @param resource $stream A stream resource.
     */
    public function __construct($stream)
    {
        Assert::resource($stream, 'stream');

        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (null === $this->stream) {
            throw new IOException('Cannot read from a closed input.');
        }

        if (false === fwrite($this->stream, $string)) {
            throw new IOException('Could not write stream.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (null === $this->stream) {
            throw new IOException('Cannot read from a closed input.');
        }

        if (false === fflush($this->stream)) {
            throw new IOException('Could not flush stream.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAnsi()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI');
        }

        return function_exists('posix_isatty') && @posix_isatty($this->stream);
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
