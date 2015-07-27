<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\IO\InputStream;

/**
 * An input stream that reads from the standard input.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StandardInputStream extends StreamInputStream
{
    /**
     * Creates the input.
     */
    public function __construct()
    {
        parent::__construct(fopen('php://stdin', 'r'));
    }
}
