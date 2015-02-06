<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Adapter;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Console\Api\Input\CommandName;
use Webmozart\Console\Api\Input\CommandOption;

/**
 * Adapts the input definition API in this package to Symfony's
 * {@link InputDefinition} API.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputDefinitionAdapter extends InputDefinition
{
    /**
     * @var string[]
     */
    private $commandNames = array();

    /**
     * Creates a new adapter.
     *
     * @param \Webmozart\Console\Api\Input\InputDefinition $adaptedDefinition The adapted input definition.
     */
    public function __construct(\Webmozart\Console\Api\Input\InputDefinition $adaptedDefinition)
    {
        $i = 1;

        foreach ($adaptedDefinition->getCommandNames() as $commandName) {
            $this->addArgument($argument = $this->adaptCommandName($commandName, $i++));

            $this->commandNames[$argument->getName()] = $commandName->toString();
        }

        foreach ($adaptedDefinition->getCommandOptions() as $commandOption) {
            $this->addOption($this->adaptCommandOption($commandOption));
        }

        foreach ($adaptedDefinition->getOptions() as $option) {
            $this->addOption($this->adaptOption($option));
        }

        foreach ($adaptedDefinition->getArguments() as $argument) {
            $this->addArgument($this->adaptArgument($argument));
        }
    }

    /**
     * Returns the command names indexed by their argument names.
     *
     * @return string[] The command names.
     */
    public function getCommandNames()
    {
        return $this->commandNames;
    }

    /**
     * Creates an input argument for the given command name.
     *
     * @param CommandName $commandName The command name to adapt.
     * @param int         $index       The index of the command name.
     *
     * @return InputArgument The created input argument.
     */
    private function adaptCommandName(CommandName $commandName, $index)
    {
        return new InputArgument('cmd'.$index, InputArgument::REQUIRED);
    }

    /**
     * Creates an input option for the given command option.
     *
     * @param CommandOption $commandOption The command option.
     *
     * @return InputOption The created input option.
     */
    private function adaptCommandOption(CommandOption $commandOption)
    {
        return new InputOption($commandOption->getLongName(), $commandOption->getShortName());
    }

    /**
     * Creates an input option for the given input option.
     *
     * @param \Webmozart\Console\Api\Input\InputOption $option The input option.
     *
     * @return InputOption The created input option.
     */
    private function adaptOption(\Webmozart\Console\Api\Input\InputOption $option)
    {
        $mode = null;

        if ($option->isMultiValued()) {
            $mode |= InputOption::VALUE_IS_ARRAY;
        }

        if ($option->isValueOptional()) {
            $mode |= InputOption::VALUE_OPTIONAL;
        }

        if ($option->isValueRequired()) {
            $mode |= InputOption::VALUE_REQUIRED;
        }

        return new InputOption($option->getLongName(), $option->getShortName(), $mode, '', $option->getDefaultValue());
    }

    /**
     * Creates an input argument for the given input argument.
     *
     * @param \Webmozart\Console\Api\Input\InputArgument $argument The input argument.
     *
     * @return InputArgument The created input argument.
     */
    private function adaptArgument(\Webmozart\Console\Api\Input\InputArgument $argument)
    {
        $mode = null;

        if ($argument->isMultiValued()) {
            $mode |= InputArgument::IS_ARRAY;
        }

        if ($argument->isOptional()) {
            $mode |= InputArgument::OPTIONAL;
        }

        if ($argument->isRequired()) {
            $mode |= InputArgument::REQUIRED;
        }

        return new InputArgument($argument->getName(), $mode, '', $argument->getDefaultValue());
    }
}
