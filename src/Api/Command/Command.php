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

use Exception;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\CannotParseArgsException;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\Config\SubCommandConfig;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Util\ProcessTitle;

/**
 * A console command.
 *
 * A `Command` object contains all the information that is necessary to describe
 * and run a console command. Use the {@link CommandConfig} class to configure
 * a command:
 *
 * ```php
 * $config = CommandConfig::create()
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
 * @see    NamedCommand
 */
class Command
{
    /**
     * @var CommandConfig
     */
    private $config;

    /**
     * @var ArgsFormat
     */
    private $argsFormat;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var Command
     */
    private $parentCommand;

    /**
     * @var CommandCollection
     */
    private $subCommands;

    /**
     * @var CommandCollection
     */
    private $optionCommands;

    /**
     * @var Command[]
     */
    private $defaultCommands = array();

    /**
     * Creates a new command.
     *
     * @param CommandConfig $config        The command configuration.
     * @param Application   $application   The console application.
     * @param Command       $parentCommand The parent command.
     */
    public function __construct(CommandConfig $config, Application $application = null, Command $parentCommand = null)
    {
        $this->config = $config;
        $this->application = $application;
        $this->parentCommand = $parentCommand;
        $this->subCommands = new CommandCollection();
        $this->optionCommands = new CommandCollection();

        $this->argsFormat = $this->buildFormat();

        foreach ($config->getSubCommandConfigs() as $subConfig) {
            $this->addSubCommand($subConfig);
        }

        foreach ($config->getOptionCommandConfigs() as $optionConfig) {
            $this->addOptionCommand($optionConfig);
        }

        foreach ($config->getDefaultCommands() as $nameOrConfig) {
            if ($nameOrConfig instanceof SubCommandConfig) {
                $this->defaultCommands[] = new Command($nameOrConfig, $this->application, $this);
            } elseif ($this->subCommands->contains($nameOrConfig)) {
                $this->defaultCommands[] = $this->subCommands->get($nameOrConfig);
            } elseif ($this->optionCommands->contains($nameOrConfig)) {
                $this->defaultCommands[] = $this->optionCommands->get($nameOrConfig);
            }
        }
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
     * Returns the arguments format of the command.
     *
     * @return ArgsFormat The input definition.
     */
    public function getArgsFormat()
    {
        return $this->argsFormat;
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
     * Returns the parent command.
     *
     * @return Command The parent command.
     */
    public function getParentCommand()
    {
        return $this->parentCommand;
    }

    /**
     * Returns all sub-commands of the command.
     *
     * @return CommandCollection The sub-commands.
     */
    public function getSubCommands()
    {
        return $this->subCommands;
    }

    /**
     * Returns the sub-command with a specific name.
     *
     * @param string $name The name of the sub-command.
     *
     * @return Command The sub-command.
     *
     * @throws NoSuchCommandException If the sub-command with the given name
     *                                does not exist.
     */
    public function getSubCommand($name)
    {
        return $this->subCommands->get($name);
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
        return $this->subCommands->contains($name);
    }

    /**
     * Returns whether the command has any sub-commands.
     *
     * @return bool Returns `true` if the command has sub-commands and `false`
     *              otherwise.
     */
    public function hasSubCommands()
    {
        return !$this->subCommands->isEmpty();
    }

    /**
     * Returns all option commands of the command.
     *
     * @return CommandCollection The option commands.
     */
    public function getOptionCommands()
    {
        return $this->optionCommands;
    }

    /**
     * Returns the option command with a specific name.
     *
     * @param string $name The name of the option command.
     *
     * @return Command The option command.
     *
     * @throws NoSuchCommandException If the option command with the given name
     *                                does not exist.
     */
    public function getOptionCommand($name)
    {
        return $this->optionCommands->get($name);
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
        return $this->optionCommands->contains($name);
    }

    /**
     * Returns whether the command has any option commands.
     *
     * @return bool Returns `true` if the command has option commands and
     *              `false` otherwise.
     */
    public function hasOptionCommands()
    {
        return !$this->optionCommands->isEmpty();
    }

    /**
     * Returns the commands that should be executed if no explicit command is
     * passed.
     *
     * @return Command[] The default commands.
     */
    public function getDefaultCommands()
    {
        return $this->defaultCommands;
    }

    /**
     * Returns whether the command has any default commands.
     *
     * @return bool Returns `true` if the command has default commands and
     *              `false` otherwise.
     *
     * @see getDefaultCommands()
     */
    public function hasDefaultCommands()
    {
        return count($this->defaultCommands) > 0;
    }

    /**
     * Parses the raw console arguments and returns the parsed arguments.
     *
     * @param RawArgs $args The raw console arguments.
     *
     * @return Args The parsed console arguments.
     *
     * @throws CannotParseArgsException If the arguments cannot be parsed.
     */
    public function parseArgs(RawArgs $args)
    {
        return $this->config->getArgsParser()->parseArgs($args, $this->argsFormat);
    }

    /**
     * Executes the command for the given unparsed arguments.
     *
     * @param RawArgs $args The unparsed console arguments.
     * @param IO      $io   The I/O.
     *
     * @return int Returns 0 on success and any other integer on error.
     */
    public function run(RawArgs $args, IO $io)
    {
        return $this->handle($this->parseArgs($args), $io);
    }

    /**
     * Executes the command for the given parsed arguments.
     *
     * @param Args $args The parsed console arguments.
     * @param IO   $io   The I/O.
     *
     * @return int Returns 0 on success and any other integer on error.
     *
     * @throws Exception
     */
    public function handle(Args $args, IO $io)
    {
        $processTitle = $this->config->getProcessTitle();
        $commandHandler = $this->config->getHandler();
        $handlerMethod = $this->config->getHandlerMethod();

        $this->warnIfProcessTitleNotSupported($processTitle, $io);

        if ($processTitle && ProcessTitle::isSupported()) {
            ProcessTitle::setProcessTitle($processTitle);

            try {
                $statusCode = $commandHandler->$handlerMethod($this, $args, $io);
            } catch (Exception $e) {
                ProcessTitle::resetProcessTitle();

                throw $e;
            }

            ProcessTitle::resetProcessTitle();
        } else {
            $statusCode = $commandHandler->$handlerMethod($this, $args, $io);
        }

        return $statusCode;
    }

    /**
     * Creates the arguments format of the command.
     *
     * @return ArgsFormat The created format for the console arguments.
     */
    protected function buildFormat()
    {
        return ArgsFormat::build($this->getBaseFormat())
            ->addOptions($this->config->getOptions())
            ->addArguments($this->config->getArguments())
            ->getFormat();
    }

    /**
     * Returns the inherited arguments format of the command.
     *
     * @return ArgsFormat The inherited format.
     *
     * @see buildFormat()
     */
    protected function getBaseFormat()
    {
        if ($this->parentCommand) {
            return $this->parentCommand->getArgsFormat();
        }

        if ($this->application) {
            return $this->application->getGlobalArgsFormat();
        }

        return null;
    }

    /**
     * Adds a sub-command.
     *
     * @param SubCommandConfig $config The sub-command configuration.
     *
     * @throws CannotAddCommandException If the command cannot be added.
     */
    private function addSubCommand(SubCommandConfig $config)
    {
        $command = new NamedCommand($config, $this->application, $this);
        $name = $command->getName();

        if ($this->subCommands->contains($name)) {
            throw CannotAddCommandException::nameExists($name);
        }

        $this->subCommands->add($command);
    }

    /**
     * Adds an option command.
     *
     * @param OptionCommandConfig $config The option command configuration.
     *
     * @throws CannotAddCommandException If the command cannot be added.
     */
    private function addOptionCommand(OptionCommandConfig $config)
    {
        $name = $config->getLongName();

        if ($this->subCommands->contains($name) || $this->optionCommands->contains($name)) {
            throw CannotAddCommandException::nameExists($name);
        }

        if ($this->argsFormat->hasOption($name)) {
            throw CannotAddCommandException::optionExists($name);
        }

        if ($shortName = $config->getShortName()) {
            if ($this->subCommands->contains($shortName) || $this->optionCommands->contains($shortName)) {
                throw CannotAddCommandException::nameExists($name);
            }

            if ($this->argsFormat->hasOption($shortName)) {
                throw CannotAddCommandException::optionExists($shortName);
            }
        }

        $this->optionCommands->add(new NamedCommand($config, $this->application, $this));
    }

    private function warnIfProcessTitleNotSupported($processTitle, IO $io)
    {
        if ($processTitle && !ProcessTitle::isSupported()) {
            $io->errorLine(
                '<comment>Install the proctitle PECL to be able to change the process title.</comment>',
                IO::VERY_VERBOSE
            );
        }
    }
}
