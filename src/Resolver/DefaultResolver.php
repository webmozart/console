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

use Webmozart\Console\Adapter\InputDefinitionAdapter;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\Resolver\CannotResolveCommandException;
use Webmozart\Console\Api\Resolver\CommandResolver;
use Webmozart\Console\Api\Resolver\ResolvedCommand;
use Webmozart\Console\Resolver\CommandResult;

/**
 * Parses the raw console arguments for the command to execute.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultResolver implements CommandResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolveCommand(RawArgs $args, Application $application)
    {
        $tokens = $args->getTokens();
        $namedCommands = $application->getNamedCommands();

        $argumentsToTest = $this->getArgumentsToTest($tokens);
        $optionsToTest = $this->getOptionsToTest($tokens);

        // Try to find a command for the passed arguments and options.
        if ($result = $this->processArguments($args, $namedCommands, $argumentsToTest, $optionsToTest)) {
            if (!$result->isParsable()) {
                throw $result->getParseError();
            }

            return new ResolvedCommand($result->getCommand(), $result->getParsedArgs());
        }

        // If arguments were passed, we did not find the matching command.
        if ($argumentsToTest) {
            throw CannotResolveCommandException::nameNotFound(reset($argumentsToTest), $namedCommands);
        }

        // If no arguments were passed, run the application's default command.
        if ($result = $this->processDefaultCommands($args, $application->getDefaultCommands())) {
            if (!$result->isParsable()) {
                throw $result->getParseError();
            }

            return new ResolvedCommand($result->getCommand(), $result->getParsedArgs());
        }

        // No default command is configured.
        throw CannotResolveCommandException::noDefaultCommand();
    }

    /**
     * @param RawArgs           $args
     * @param CommandCollection $namedCommands
     * @param string[]          $argumentsToTest
     * @param string[]          $optionsToTest
     *
     * @return ResolveResult
     */
    private function processArguments(RawArgs $args, CommandCollection $namedCommands, array $argumentsToTest, array $optionsToTest)
    {
        $currentCommand = null;

        // Parse the arguments for command names until we fail to find a
        // matching command
        foreach ($argumentsToTest as $name) {
            if (!$namedCommands->contains($name)) {
                break;
            }

            $nextCommand = $namedCommands->get($name);

            if ($nextCommand->getConfig() instanceof OptionCommandConfig) {
                break;
            }

            $currentCommand = $nextCommand;
            $namedCommands = $currentCommand->getNamedSubCommands();
        }

        if (!$currentCommand) {
            return null;
        }

        return $this->processOptions($args, $currentCommand, $optionsToTest);
    }

    /**
     * @param RawArgs  $args
     * @param Command  $currentCommand
     * @param string[] $optionsToTest
     *
     * @return ResolveResult
     */
    private function processOptions(RawArgs $args, Command $currentCommand, array $optionsToTest)
    {
        foreach ($optionsToTest as $option) {
            $commands = $currentCommand->getNamedSubCommands();

            if (!$commands->contains($option)) {
                continue;
            }

            $nextCommand = $commands->get($option);

            if (!$nextCommand->getConfig() instanceof OptionCommandConfig) {
                break;
            }

            $currentCommand = $nextCommand;
        }

        return $this->processDefaultSubCommands($args, $currentCommand);
    }

    /**
     * @param RawArgs $args
     * @param Command $currentCommand
     *
     * @return ResolveResult
     */
    private function processDefaultSubCommands(RawArgs $args, Command $currentCommand)
    {
        if ($result = $this->processDefaultCommands($args, $currentCommand->getDefaultSubCommands())) {
            return $result;
        }

        // No default commands, return the current command
        return new ResolveResult($currentCommand, $args);
    }

    /**
     * @param RawArgs           $args
     * @param CommandCollection $defaultCommands
     *
     * @return ResolveResult
     */
    private function processDefaultCommands(RawArgs $args, CommandCollection $defaultCommands)
    {
        $firstResult = null;

        foreach ($defaultCommands as $defaultCommand) {
            $resolvedCommand = new ResolveResult($defaultCommand, $args);

            if ($resolvedCommand->isParsable()) {
                return $resolvedCommand;
            }

            if (!$firstResult) {
                $firstResult = $resolvedCommand;
            }
        }

        // Return the first default command if one was found
        return $firstResult;
    }

    private function getArgumentsToTest(array &$tokens)
    {
        $argumentsToTest = array();

        for (; null !== key($tokens); next($tokens)) {
            $token = current($tokens);

            // "--" stops argument parsing
            if ('--' === $token) {
                break;
            }

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
            if (isset($token[0]) && '-' === $token[0]) {
                break;
            }

            $argumentsToTest[] = $token;
        }

        return $argumentsToTest;
    }

    private function getOptionsToTest(array &$tokens)
    {
        $optionsToTest = array();

        for (; null !== key($tokens); next($tokens)) {
            $token = current($tokens);

            // "--" stops option parsing
            if ('--' === $token) {
                break;
            }

            if (isset($token[0]) && '-' === $token[0]) {
                if ('--' === substr($token, 0, 2) && strlen($token) > 2) {
                    $optionsToTest[] = substr($token, 2);
                } elseif (2 === strlen($token)) {
                    $optionsToTest[] = substr($token, 1);
                }

                continue;
            }
        }

        return $optionsToTest;
    }
}
