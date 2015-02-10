<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Resolver;

use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Resolver\CommandNotDefinedException;
use Webmozart\Console\Api\Resolver\CommandResolver;
use Webmozart\Console\Assert\Assert;

/**
 * Checks an input for command, sub-command and option command names.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultResolver implements CommandResolver
{
    /**
     * @var string
     */
    private $defaultCommandName;

    /**
     * Creates the resolver.
     *
     * @param string $defaultCommandName The name of the default command to run
     *                                   if no explicit command is requested.
     */
    public function __construct($defaultCommandName)
    {
        Assert::string($defaultCommandName, 'The default command name must be a string. Got: %s');
        Assert::notEmpty($defaultCommandName, 'The default command name must not be empty.');

        $this->defaultCommandName = $defaultCommandName;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveCommand(Input $input, CommandCollection $commands)
    {
        list($argumentsToTest, $optionsToTest) = $this->splitInput($input);

        // Parse the arguments as far as possible to determine the command
        // to execute
        // e.g. "server add localhost"
        //                  ^-- parsing stops here
        $command = $this->getCommandForArguments($argumentsToTest, $commands);

        if (null === $command) {
            if ($argumentsToTest) {
                throw CommandNotDefinedException::forCommandName(reset($argumentsToTest), $commands);
            }

            // If no arguments were passed, return the default command
            $command = $commands->get($this->defaultCommandName);
        }

        // Check whether the found command has a default sub-command
        // e.g. "server" could default to "server list"
        if ($result = $command->getDefaultSubCommand()) {
            $command = $result;
        }

        // Check whether we can find an option command for the current command
        // e.g. "server --list"
        if ($result = $this->getCommandForOptions($optionsToTest, $command)) {
            $command = $result;
        } elseif ($result = $command->getDefaultOptionCommand()) {
            // If no option command was passed, check whether a default option
            // command is set
            // e.g. "server" could default to "server --list"
            $command = $result;
        }

        return $command;
    }

    private function splitInput(Input $input)
    {
        $parts = explode(' ', $input->toString());
        $argumentsToTest = array();
        $optionsToTest = array();
        $parseArguments = true;

        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }

            // "--" stops option parsing
            if ('--' === $part) {
                break;
            }

            if (isset($part[0]) && '-' === $part[0]) {
                // Stop argument parsing when we reach the first option.

                // Command names must be passed before any option. The reason
                // is that we cannot determine whether an argument after an
                // option is the value of that option or an argument by itself
                // without getting the input definition of the corresponding
                // command first.

                // For example, in the command "server -f add" we don't know
                // whether "add" is the value of the "-f" option or an argument.
                // Hence we stop argument parsing after "-f" and assume that
                // "server" (or "server -f") is the command to execute.

                $parseArguments = false;

                if ('--' === substr($part, 0, 2) && strlen($part) > 2) {
                    $optionsToTest[] = substr($part, 2);
                } elseif (2 === strlen($part)) {
                    $optionsToTest[] = substr($part, 1);
                }

                continue;
            }

            if ($parseArguments) {
                $argumentsToTest[] = $part;
            }
        }

        return array($argumentsToTest, $optionsToTest);
    }

    private function getCommandForArguments(array $argumentsToTest, CommandCollection $possibleCommands)
    {
        $command = null;

        // Parse the arguments for command names until we fail to find a
        // matching command
        foreach ($argumentsToTest as $argument) {
            if (!$possibleCommands->contains($argument)) {
                break;
            }

            $command = $possibleCommands->get($argument);

            // Search the sub-commands in the next iteration
            $possibleCommands = $command->getSubCommands();
        }

        return $command;
    }

    private function getCommandForOptions(array $optionsToTest, Command $currentCommand)
    {
        $command = null;

        foreach ($optionsToTest as $option) {
            $possibleCommands = $currentCommand->getOptionCommands();

            if (!$possibleCommands->contains($option)) {
                continue;
            }

            $command = $possibleCommands->get($option);
        }

        return $command;
    }
}
