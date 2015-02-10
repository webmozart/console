<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Input;

use Symfony\Component\Console\Input\InputInterface;

/**
 * The console input.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Input extends InputInterface
{
    /**
     * Returns the input as string.
     *
     * @return string The input as string.
     */
    public function toString();
}
