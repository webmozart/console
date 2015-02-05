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

use Webmozart\Console\Assert\Assert;

/**
 * A command name in the input definition.
 *
 * The command name determines which command should be executed. An input
 * definition may contain one or several command names.
 *
 * In the example below, the input contains the two command names "server" and
 * "add":
 *
 * ```
 * $ console server add localhost
 * ```
 *
 * The last part "localhost" is the argument to the "server add" command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see    CommandOption, InputDefinition
 */
class CommandName
{
    /**
     * @var string
     */
    private $string;

    /**
     * Creates a new command name.
     *
     * @param string $string The command name.
     */
    public function __construct($string)
    {
        Assert::string($string, 'The command name must be a string. Got: %s');
        Assert::notEmpty($string, 'The command name must not be empty.');
        Assert::regex($string, '~^[a-zA-Z0-9\-]+$~', 'The command name must contain letters, digits and hyphens only. Got: "%s"');

        $this->string = $string;
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
     * Casts the command name to a string.
     *
     * @return string The command name.
     */
    public function __toString()
    {
        return $this->string;
    }
}
