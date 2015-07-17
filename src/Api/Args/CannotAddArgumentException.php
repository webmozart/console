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
 * Thrown when an argument cannot be added.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CannotAddArgumentException extends RuntimeException
{
    /**
     * Code: The argument exists already.
     */
    const EXISTS_ALREADY = 1;

    /**
     * Code: An argument was added after a multi-valued argument.
     */
    const MULTI_VALUED_EXISTS = 2;

    /**
     * Code: A required argument was added after an optional argument.
     */
    const REQUIRED_AFTER_OPTIONAL = 3;

    /**
     * Creates an exception with code {@link EXISTS_ALREADY}.
     *
     * @param string    $name  The argument name.
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function existsAlready($name, Exception $cause = null)
    {
        return new static(sprintf(
            'An argument named "%s" exists already.',
            $name
        ), self::EXISTS_ALREADY, $cause);
    }

    /**
     * Creates an exception with code {@link ADD_AFTER_MULTI_VALUED}.
     *
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function cannotAddAfterMultiValued(Exception $cause = null)
    {
        return new static(
            'Cannot add an argument after a multi-valued argument.',
            self::MULTI_VALUED_EXISTS,
            $cause
        );
    }

    /**
     * Creates an exception with code {@link ADD_REQUIRED_AFTER_OPTIONAL}.
     *
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function cannotAddRequiredAfterOptional(Exception $cause = null)
    {
        return new static(
            'Cannot add a required argument after an optional one.',
            self::REQUIRED_AFTER_OPTIONAL,
            $cause
        );
    }
}
