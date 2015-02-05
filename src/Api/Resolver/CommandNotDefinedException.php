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
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandNotDefinedException extends RuntimeException
{
    /**
     * @var string[]
     */
    private $suggestedNames = array();

    /**
     * Creates an exception for the given command name.
     *
     * Suggested alternatives are searched in the passed commands.
     *
     * @param string            $commandName The command name that was not found.
     * @param CommandCollection $commands    A list of available commands that
     *                                       is searched for similar names.
     * @param int               $code        The exception code.
     * @param Exception         $cause       The exception that caused this
     *                                       exception.
     *
     * @return static The created exception.
     */
    public static function forCommandName($commandName, CommandCollection $commands, $code = 0, Exception $cause = null)
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

        return new static($message, $suggestedNames, $code, $cause);
    }

    /**
     * Creates an exception.
     *
     * @param string    $message        The exception message.
     * @param string[]  $suggestedNames The suggested command names.
     * @param int       $code           The exception code.
     * @param Exception $cause          The exception that caused this exception.
     */
    public function __construct($message = '', array $suggestedNames = array(), $code = 0, Exception $cause = null)
    {
        parent::__construct($message, $code, $cause);

        $this->suggestedNames = $suggestedNames;
    }

    /**
     * Returns the suggested command names.
     *
     * @return string[] The suggested command names.
     */
    public function getSuggestedCommandNames()
    {
        return $this->suggestedNames;
    }
}
