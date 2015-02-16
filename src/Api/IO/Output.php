<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\IO;

use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Api\Formatter\StyleSet;

/**
 * The console output.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Output
{
    /**
     * Writes a string to the output.
     *
     * @param string $string The string to write.
     *
     * @throws IOException If writing fails or if the output is closed.
     */
    public function write($string);

    /**
     * Flushes the output and forces all pending text to be written out.
     *
     * @throws IOException If flushing fails or if the output is closed.
     */
    public function flush();

    /**
     * Returns whether the output supports ANSI format codes.
     *
     * @return bool Returns `true` if the output supports ANSI format codes and
     *              `false` otherwise.
     */
    public function supportsAnsi();

    /**
     * Closes the output.
     */
    public function close();
}
