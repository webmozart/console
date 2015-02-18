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
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\CommandName;
use Webmozart\Console\Api\Args\Format\CommandOption;
use Webmozart\Console\Api\Args\Format\Option;

/**
 * Adapts an {@link ArgsFormat} instance to Symfony's {@link InputDefinition}
 * API.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgsFormatAdapter extends InputDefinition
{
    /**
     * @var CommandName[]
     */
    private $commandNames = array();

    /**
     * Creates a new adapter.
     *
     * @param ArgsFormat $format The adapted format.
     */
    public function __construct(ArgsFormat $format)
    {
        parent::__construct();

        $i = 1;

        foreach ($format->getCommandNames() as $commandName) {
            do { $argName = 'cmd'.$i++; } while ($format->hasArgument($argName));

            $this->addArgument($argument = $this->adaptCommandName($commandName, $argName));

            $this->commandNames[$argument->getName()] = $commandName;
        }

        foreach ($format->getCommandOptions() as $commandOption) {
            $this->addOption($this->adaptCommandOption($commandOption));
        }

        foreach ($format->getOptions() as $option) {
            $this->addOption($this->adaptOption($option));
        }

        foreach ($format->getArguments() as $argument) {
            $this->addArgument($this->adaptArgument($argument));
        }
    }

    /**
     * Returns the command names indexed by their argument names.
     *
     * @return CommandName[] The command names.
     */
    public function getCommandNamesByArgumentName()
    {
        return $this->commandNames;
    }

    /**
     * Creates an input argument for the given command name.
     *
     * @param CommandName $commandName The command name.
     * @param string      $argName     The name of the added argument.
     *
     * @return InputArgument The created input argument.
     */
    private function adaptCommandName(CommandName $commandName, $argName)
    {
        return new InputArgument($argName, InputArgument::REQUIRED);
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
     * Creates an input option for the given option.
     *
     * @param Option $option The option.
     *
     * @return InputOption The created input option.
     */
    private function adaptOption(Option $option)
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

        return new InputOption($option->getLongName(), $option->getShortName(), $mode, $option->getDescription(), $option->getDefaultValue());
    }

    /**
     * Creates an input argument for the given argument.
     *
     * @param Argument $argument The argument.
     *
     * @return InputArgument The created input argument.
     */
    private function adaptArgument(Argument $argument)
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

        return new InputArgument($argument->getName(), $mode, $argument->getDescription(), $argument->getDefaultValue());
    }
}
