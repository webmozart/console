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

use Webmozart\Console\Api\IO\Input;

/**
 * An input that does nothing.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullInput implements Input
{
    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function readLine($length = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }
}
