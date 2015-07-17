<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Args;

use Exception;
use RuntimeException;

/**
 * Thrown when a non-existing option is accessed.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NoSuchOptionException extends RuntimeException
{
    /**
     * Creates an exception for the given option name.
     *
     * @param string    $name  The option name.
     * @param int       $code  The exception code.
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function forOptionName($name, $code = 0, Exception $cause = null)
    {
        return new static(sprintf(
            'The option "%s%s" does not exist.',
            strlen($name) > 1 ? '--' : '-',
            $name
        ), $code, $cause);
    }
}
