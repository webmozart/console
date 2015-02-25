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
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\CannotParseArgsException;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\Config\SubCommandConfig;
use Webmozart\Console\Api\Event\ConsoleEvents;
use Webmozart\Console\Api\Event\PreHandleEvent;
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
    private $namedSubCommands;

    /**
     * @var CommandCollection
     */
    private $defaultSubCommands;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Creates a new command.
     *
     * @param CommandConfig $config        The command configuration.
     * @param Application   $application   The console application.
     * @param Command       $parentCommand The parent command.
     *
     * @throws LogicException If the name of the command configuration is not set.
     */
    public function __construct(CommandConfig $config, Application $application = null, Command $parentCommand = null)
    {
        if (!$config->getName()) {
            throw new LogicException('The name of the command config must be set.');
        }

        $this->name = $config->getName();
        $this->shortName = $config instanceof OptionCommandConfig ? $config->getShortName() : null;
        $this->aliases = $config->getAliases();
        $this->config = $config;
        $this->application = $application;
        $this->parentCommand = $parentCommand;
        $this->subCommands = new CommandCollection();
        $this->namedSubCommands = new CommandCollection();
        $this->defaultSubCommands = new CommandCollection();
        $this->argsFormat = $config->buildArgsFormat($this->getBaseFormat());
        $this->dispatcher = $application ? $application->getConfig()->getEventDispatcher() : null;

        foreach ($config->getSubCommandConfigs() as $subConfig) {
            $this->addSubCommand($subConfig);
        }
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
     * Returns whether the command has aliases.
     *
     * @return bool Returns `true` if the command has aliases and `false`
     *              otherwise.
     */
    public function hasAliases()
    {
        return count($this->aliases) > 0;
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
     * Returns all sub-commands that are not anonymous.
     *
     * @return CommandCollection The named commands.
     */
    public function getNamedSubCommands()
    {
        return $this->namedSubCommands;
    }

    /**
     * Returns whether the command has any commands that are not anonymous.
     *
     * @return bool Returns `true` if the command has named commands and
     *              `false` otherwise.
     *
     * @see getNamedSubCommands()
     */
    public function hasNamedSubCommands()
    {
        return count($this->namedSubCommands) > 0;
    }

    /**
     * Returns the commands that should be executed if no explicit command is
     * passed.
     *
     * @return CommandCollection The default commands.
     */
    public function getDefaultSubCommands()
    {
        return $this->defaultSubCommands;
    }

    /**
     * Returns whether the command has any default commands.
     *
     * @return bool Returns `true` if the command has default commands and
     *              `false` otherwise.
     *
     * @see getDefaultSubCommands()
     */
    public function hasDefaultSubCommands()
    {
        return count($this->defaultSubCommands) > 0;
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

        $this->warnIfProcessTitleNotSupported($processTitle, $io);

        if ($processTitle && ProcessTitle::isSupported()) {
            ProcessTitle::setProcessTitle($processTitle);

            try {
                $statusCode = $this->doHandle($args, $io);
            } catch (Exception $e) {
                ProcessTitle::resetProcessTitle();

                throw $e;
            }

            ProcessTitle::resetProcessTitle();
        } else {
            $statusCode = $this->doHandle($args, $io);
        }

        return $statusCode;
    }

    /**
     * Returns the inherited arguments format of the command.
     *
     * @return ArgsFormat The inherited format.
     *
     * @see CommandConfig::buildArgsFormat()
     */
    private function getBaseFormat()
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
        if (!$config->isEnabled()) {
            return;
        }

        $this->validateSubCommandName($config);

        $command = new Command($config, $this->application, $this);

        $this->subCommands->add($command);

        if ($config->isDefault()) {
            $this->defaultSubCommands->add($command);
        }

        if (!$config->isAnonymous()) {
            $this->namedSubCommands->add($command);
        }
    }

    private function warnIfProcessTitleNotSupported($processTitle, IO $io)
    {
        if ($processTitle && !ProcessTitle::isSupported()) {
            $io->errorLine(
                '<warn>Notice: Install the proctitle PECL to be able to change the process title.</warn>',
                IO::VERY_VERBOSE
            );
        }
    }

    private function validateSubCommandName(SubCommandConfig $config)
    {
        $name = $config->getName();

        if (!$name) {
            throw CannotAddCommandException::nameEmpty();
        }

        if ($this->subCommands->contains($name)) {
            throw CannotAddCommandException::nameExists($name);
        }

        if ($config instanceof OptionCommandConfig) {
            if ($this->argsFormat->hasOption($name)) {
                throw CannotAddCommandException::optionExists($name);
            }

            if ($shortName = $config->getShortName()) {
                if ($this->subCommands->contains($shortName)) {
                    throw CannotAddCommandException::nameExists($name);
                }

                if ($this->argsFormat->hasOption($shortName)) {
                    throw CannotAddCommandException::optionExists($shortName);
                }
            }
        }
    }

    private function doHandle(Args $args, IO $io)
    {
        if ($this->dispatcher && $this->dispatcher->hasListeners(ConsoleEvents::PRE_HANDLE)) {
            $event = new PreHandleEvent($args, $io, $this);
            $this->dispatcher->dispatch(ConsoleEvents::PRE_HANDLE, $event);

            if ($event->isHandled()) {
                return $event->getStatusCode();
            }
        }

        $commandHandler = $this->config->getHandler();
        $handlerMethod = $this->config->getHandlerMethod();
        $statusCode = $commandHandler->$handlerMethod($args, $io, $this);

        return (int) $statusCode;
    }
}
