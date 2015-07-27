<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\IO\OutputStream;

/**
 * An output stream that writes to the error output.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ErrorOutputStream extends StreamOutputStream
{
    /**
     * Creates the stream.
     */
    public function __construct()
    {
        parent::__construct(fopen('php://stderr', 'w'));
    }
}
