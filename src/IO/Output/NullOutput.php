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

use Webmozart\Console\Api\IO\Output;

/**
 * An output that does nothing.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullOutput implements Output
{
    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAnsi()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }
}
