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
 * Thrown when two commands have the same name.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CannotAddCommandException extends RuntimeException
{
    /**
     * Code: A command with the same name exists.
     */
    const NAME_EXISTS = 1;

    /**
     * Code: An option with the same name exists.
     */
    const OPTION_EXISTS = 2;

    /**
     * Code: The command name is empty.
     */
    const NAME_EMPTY = 3;

    /**
     * Creates an exception for the code {@link NAME_EXISTS}.
     *
     * @param string    $name  The command name.
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function nameExists($name, Exception $cause = null)
    {
        return new static(sprintf(
            'A command named "%s" exists already.',
            $name
        ), self::NAME_EXISTS, $cause);
    }

    /**
     * Creates an exception for the code {@link OPTION_EXISTS}.
     *
     * @param string    $name  The command name.
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function optionExists($name, Exception $cause = null)
    {
        return new static(sprintf(
            'An option named "%s%s" exists already.',
            strlen($name) > 1 ? '--' : '-',
            $name
        ), self::OPTION_EXISTS, $cause);
    }

    /**
     * Creates an exception for the code {@link NAME_EMPTY}.
     *
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function nameEmpty(Exception $cause = null)
    {
        return new static('The command name must be set.', self::NAME_EMPTY, $cause);
    }
}
