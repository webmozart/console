<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Args\Format;

use Webmozart\Assert\Assert;

/**
 * A command name in the console arguments.
 *
 * The command name determines which command should be executed. The console
 * input may contain one or several command names.
 *
 * In the example below, the console arguments contain the two command names
 * "server" and "add":
 *
 * ```
 * $ console server add localhost
 * ```
 *
 * The last part "localhost" is the argument to the "server add" command.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @see    CommandOption, ArgsFormat
 */
class CommandName
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var string[]
     */
    private $aliases;

    /**
     * Creates a new command name.
     *
     * @param string   $string  The command name.
     * @param string[] $aliases The alias names.
     */
    public function __construct($string, array $aliases = array())
    {
        Assert::string($string, 'The command name must be a string. Got: %s');
        Assert::notEmpty($string, 'The command name must not be empty.');
        Assert::regex($string, '~^[a-zA-Z0-9\-]+$~', 'The command name must contain letters, digits and hyphens only. Got: "%s"');

        Assert::allString($aliases, 'The command aliases must be strings. Got: %s');
        Assert::allNotEmpty($aliases, 'The command aliases must not be empty.');
        Assert::allRegex($aliases, '~^[a-zA-Z0-9\-]+$~', 'The command aliases must contain letters, digits and hyphens only. Got: "%s"');

        $this->string = $string;
        $this->aliases = $aliases;
    }

    /**
     * Returns the command name as string.
     *
     * @return string The command name.
     */
    public function toString()
    {
        return $this->string;
    }

    /**
     * Returns the alias names.
     *
     * @return string[] The aliases of the command name.
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Returns whether a string matches the command name or one of its aliases.
     *
     * @param string $string The string to test.
     *
     * @return bool Returns `true` if the given string matches the command name
     *              or one of its aliases and `false` otherwise.
     */
    public function match($string)
    {
        return $this->string === $string || in_array($string, $this->aliases, true);
    }

    /**
     * Casts the command name to a string.
     *
     * @return string The command name.
     */
    public function __toString()
    {
        return $this->string;
    }
}
