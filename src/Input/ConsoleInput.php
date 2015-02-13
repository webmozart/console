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

/**
 * An input that reads from the console.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ConsoleInput extends StreamInput
{
    /**
     * Creates a new input.
     */
    public function __construct()
    {
        parent::__construct(fopen('php://stdin', 'r'));
    }
}
