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

use LogicException;
use OutOfBoundsException;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\Input\CommandName;
use Webmozart\Console\Api\Input\CommandOption;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Input\InputDefinitionBuilder;

/**
 * A console command.
 *
 * A `Command` object contains all the information that is necessary to describe
 * and run a console command. Use the {@link CommandConfig} class to configure
 * a command:
 *
 * ```php
 * $config = CommandConfig::create()
 *     ->setName('server')
 *     ->setDescription('List and manage servers')
 *
 *     ->beginSubCommand('add')
 *         ->setDescription('Add a new server')
 *         ->addArgument('host', InputArgument::REQUIRED)
 *         ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, null, 80)
 *     ->end()
 * ;
 *
 * $command = new Command($config);
 * ```
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Command
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $shortName;

    /**
     * @var string[]
     */
    private $aliases = array();

    /**
     * @var CommandConfig
     */
    private $config;

    /**
     * @var InputDefinition
     */
    private $inputDefinition;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var Command[]
     */
    private $subCommands;

    /**
     * @var Command[]
     */
    private $optionCommands;

    /**
     * Creates a new command.
     *
     * You can optionally pass a base input definition. The command will then
     * inherit all the arguments and options of the base definition.
     *
     * @param CommandConfig   $config              The command configuration.
     * @param InputDefinition $baseInputDefinition The input definition to
     *                                             inherit options and arguments
     *                                             from.
     * @param Application     $application         The console application.
     *
     * @throws LogicException If the name of the command configuration is not set.
     */
    public function __construct(CommandConfig $config, InputDefinition $baseInputDefinition = null, Application $application = null)
    {
        if (!$config->getName()) {
            throw new LogicException('The name of the command config must be set.');
        }

        $definitionBuilder = new InputDefinitionBuilder($baseInputDefinition);
        $argumentCommands = array();
        $optionCommands = array();

        if ($config instanceof OptionCommandConfig) {
            $definitionBuilder->addCommandOption(new CommandOption($config->getName(), $config->getShortName()));
        } else {
            $definitionBuilder->addCommandName(new CommandName($config->getName()));
        }

        $definitionBuilder->addOptions($config->getOptions());
        $definitionBuilder->addArguments($config->getArguments());
        $inputDefinition = $definitionBuilder->getDefinition();

        foreach ($config->getSubCommandConfigs() as $subConfig) {
            $argumentCommands[$subConfig->getName()] = new Command($subConfig, $inputDefinition, $application);
        }

        foreach ($config->getOptionCommandConfigs() as $subConfig) {
            $optionCommands[$subConfig->getName()] = new Command($subConfig, $inputDefinition, $application);
        }

        $this->name = $config->getName();
        $this->shortName = $config instanceof OptionCommandConfig ? $config->getShortName() : null;
        $this->aliases = $config->getAliases();
        $this->config = $config;
        $this->inputDefinition = $inputDefinition;
        $this->application = $application;
        $this->subCommands = $argumentCommands;
        $this->optionCommands = $optionCommands;
    }

    /**
     * Returns the name of the command.
     *
     * @return string The name of the command.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the short name of the command.
     *
     * This method only returns a value if an {@link OptionCommandConfig} was
     * passed to the constructor. Otherwise this method returns `null`.
     *
     * @return string|null The short name or `null` if the command is not an
     *                     option command.
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Returns the alias names of the command.
     *
     * @return string[] An array of alias names of the command.
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Returns the configuration of the command.
     *
     * @return CommandConfig The command configuration.
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the input definition of the command.
     *
     * @return InputDefinition The input definition.
     */
    public function getInputDefinition()
    {
        return $this->inputDefinition;
    }

    /**
     * Returns the console application.
     *
     * @return Application The console application.
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Returns all sub-commands of the command.
     *
     * @return CommandCollection The sub-commands.
     */
    public function getSubCommands()
    {
        return new CommandCollection($this->subCommands);
    }

    /**
     * Returns the sub-command with a specific name.
     *
     * @param string $name The name of the sub-command.
     *
     * @return Command The sub-command.
     *
     * @throws OutOfBoundsException If the sub-command with the given name does
     *                              not exist.
     */
    public function getSubCommand($name)
    {
        if (!isset($this->subCommands[$name])) {
            throw new OutOfBoundsException(sprintf(
                'The sub-command named "%s" does not exist.',
                $name
            ));
        }

        return $this->subCommands[$name];
    }

    /**
     * Returns whether a sub-command with the given name exists.
     *
     * @param string $name The name of the sub-command.
     *
     * @return bool Returns `true` if a sub-command with that name exists and
     *              `false` otherwise.
     */
    public function hasSubCommand($name)
    {
        return isset($this->subCommands[$name]);
    }

    /**
     * Returns whether the command has any sub-commands.
     *
     * @return bool Returns `true` if the command has sub-commands and `false`
     *              otherwise.
     */
    public function hasSubCommands()
    {
        return count($this->subCommands) > 0;
    }

    /**
     * Returns the sub-command that should be executed if no explicit
     * sub-command is passed.
     *
     * @return Command|null The sub-command or `null` if this command should
     *                      be executed when no sub-command is passed.
     */
    public function getDefaultSubCommand()
    {
        if ($commandName = $this->config->getDefaultSubCommand()) {
            return $this->subCommands[$commandName];
        }

        return null;
    }

    /**
     * Returns all option commands of the command.
     *
     * @return CommandCollection The option commands.
     */
    public function getOptionCommands()
    {
        return new CommandCollection($this->optionCommands);
    }

    /**
     * Returns the option command with a specific name.
     *
     * @param string $name The name of the option command.
     *
     * @return Command The option command.
     *
     * @throws OutOfBoundsException If the option command with the given name
     *                              does not exist.
     */
    public function getOptionCommand($name)
    {
        if (!isset($this->optionCommands[$name])) {
            throw new OutOfBoundsException(sprintf(
                'The option command named "%s" does not exist.',
                $name
            ));
        }

        return $this->optionCommands[$name];
    }

    /**
     * Returns whether an option command with the given name exists.
     *
     * @param string $name The name of the option command.
     *
     * @return bool Returns `true` if an option command with that name exists
     *              and `false` otherwise.
     */
    public function hasOptionCommand($name)
    {
        return isset($this->optionCommands[$name]);
    }

    /**
     * Returns whether the command has any option commands.
     *
     * @return bool Returns `true` if the command has option commands and
     *              `false` otherwise.
     */
    public function hasOptionCommands()
    {
        return count($this->optionCommands) > 0;
    }

    /**
     * Returns the option command that should be executed if no explicit option
     * command is passed.
     *
     * @return Command|null The option command or `null` if this command should
     *                      be executed when no option command is passed.
     */
    public function getDefaultOptionCommand()
    {
        if ($commandName = $this->config->getDefaultOptionCommand()) {
            return $this->optionCommands[$commandName];
        }

        return null;
    }
}
