<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Command;

use Exception;
use RuntimeException;

/**
 * Thrown when a command was not found.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NoSuchCommandException extends RuntimeException
{
    /**
     * Creates an exception for the given command name.
     *
     * @param string    $name  The command name.
     * @param int       $code  The exception code.
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function forCommandName($name, $code = 0, Exception $cause = null)
    {
        return new static(sprintf(
            'The command "%s" does not exist.',
            $name
        ), $code, $cause);
    }
}
