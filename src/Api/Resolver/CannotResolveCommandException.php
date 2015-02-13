<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Resolver;

use Exception;
use RuntimeException;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Util\SimilarCommandName;

/**
 * Thrown when a command cannot be resolved.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CannotResolveCommandException extends RuntimeException
{
    /**
     * Code: The passed command name was not found.
     */
    const NAME_NOT_FOUND = 1;

    /**
     * Code: No command was passed and no default was configured.
     */
    const NO_DEFAULT_COMMAND = 2;

    /**
     * Creates an exception for the code {@link NAME_NOT_FOUND}.
     *
     * Suggested alternatives are searched in the passed commands.
     *
     * @param string            $commandName The command name that was not found.
     * @param CommandCollection $commands    A list of available commands that
     *                                       is searched for similar names.
     * @param Exception         $cause       The exception that caused this
     *                                       exception.
     *
     * @return static The created exception.
     */
    public static function nameNotFound($commandName, CommandCollection $commands, Exception $cause = null)
    {
        $message = sprintf('The command "%s" is not defined.', $commandName);

        $suggestedNames = SimilarCommandName::find($commandName, $commands);

        if (count($suggestedNames) > 0) {
            if (1 === count($suggestedNames)) {
                $message .= "\n\nDid you mean this?\n    ";
            } else {
                $message .= "\n\nDid you mean one of these?\n    ";
            }
            $message .= implode("\n    ", $suggestedNames);
        }

        return new static($message, self::NAME_NOT_FOUND, $cause);
    }

    /**
     * Creates an exception for the code {@link NO_DEFAULT_COMMAND}.
     *
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function noDefaultCommand(Exception $cause = null)
    {
        return new static('No default command is defined.', self::NO_DEFAULT_COMMAND, $cause);
    }
}
