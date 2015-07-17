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
 * A command option in the console arguments.
 *
 * The command names and command options determine which command is executed.
 *
 * In the example below, the console arguments contain the command name "server"
 * and the command option "delete":
 *
 * ```
 * $ console server --delete localhost
 * $ console server -d localhost
 * ```
 *
 * The last part "localhost" is the argument to the "server --delete" command.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @see    CommandName, ArgsFormat
 */
class CommandOption extends AbstractOption
{
    /**
     * @var string[]
     */
    private $longAliases = array();

    /**
     * @var string[]
     */
    private $shortAliases = array();

    /**
     * Creates the command option.
     *
     * @param string      $longName    The long option name.
     * @param string|null $shortName   The short option name.
     * @param string[]    $aliases     A list of alias names.
     * @param int         $flags       A bitwise combination of the option flag
     *                                 constants.
     * @param string      $description A human-readable description of the option.
     *
     * @throws InvalidValueException If the default value is invalid.
     */
    public function __construct($longName, $shortName = null, array $aliases = array(), $flags = 0, $description = null)
    {
        parent::__construct($longName, $shortName, $flags, $description);

        foreach ($aliases as $key => $alias) {
            $alias = $this->removeDashPrefix($alias);

            if (1 === strlen($alias)) {
                $this->assertShortAliasValid($alias);
                $this->shortAliases[] = $alias;
            } else {
                $this->assertLongAliasValid($alias);
                $this->longAliases[] = $alias;
            }
        }
    }

    /**
     * Returns all long alias names.
     *
     * @return string[] The long alias names.
     */
    public function getLongAliases()
    {
        return $this->longAliases;
    }

    /**
     * Returns all short alias names.
     *
     * @return string[] The short alias names.
     */
    public function getShortAliases()
    {
        return $this->shortAliases;
    }

    private function removeDashPrefix($string)
    {
        if ('--' === substr($string, 0, 2)) {
            $string = substr($string, 2);
        } elseif (isset($string[0]) && '-' === $string[0]) {
            $string = substr($string, 1);
        }

        return $string;
    }

    private function assertLongAliasValid($alias)
    {
        Assert::string($alias, 'An option alias must be a string or null. Got: %s');
        Assert::notEmpty($alias, 'An option alias must not be empty.');
        Assert::startsWithLetter($alias, 'A long option alias must start with a letter.');
        Assert::regex($alias, '~^[a-zA-Z0-9\-]+$~', 'A long option alias must contain letters, digits and hyphens only.');
    }

    private function assertShortAliasValid($alias)
    {
        Assert::string($alias, 'An option alias must be a string or null. Got: %s');
        Assert::notEmpty($alias, 'An option alias must not be empty.');
        Assert::regex($alias, '~^[a-zA-Z]$~', 'A short option alias must be exactly one letter. Got: "%s"');
    }
}
