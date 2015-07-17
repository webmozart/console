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

/**
 * An output that writes to the standard output.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StandardOutput extends StreamOutput
{
    /**
     * Creates the output.
     */
    public function __construct()
    {
        // From \Symfony\Component\Console\Output\ConsoleOutput
        //
        // Returns true if current environment supports writing console output
        // to STDOUT.
        //
        // IBM iSeries (OS400) exhibits character-encoding issues when writing
        // to STDOUT and doesn't properly convert ASCII to EBCDIC, resulting in
        // garbage output.

        $stream = 'OS400' === php_uname('s') ? 'php://output' : 'php://stdout';

        parent::__construct(fopen($stream, 'w'));
    }
}
