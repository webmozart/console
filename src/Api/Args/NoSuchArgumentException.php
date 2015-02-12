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
 * Thrown when a non-existing argument is accessed.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NoSuchArgumentException extends RuntimeException
{
    /**
     * Creates an exception for the given argument name.
     *
     * @param string    $name  The argument name.
     * @param int       $code  The exception code.
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function forArgumentName($name, $code = 0, Exception $cause = null)
    {
        return new static(sprintf(
            'The argument "%s" does not exist.',
            $name
        ), $code, $cause);
    }

    /**
     * Creates an exception for the given argument position.
     *
     * @param int       $position The argument position.
     * @param int       $code     The exception code.
     * @param Exception $cause    The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function forPosition($position, $code = 0, Exception $cause = null)
    {
        return new static(sprintf(
            'The argument at position %s does not exist.',
            $position
        ), $code, $cause);
    }
}
