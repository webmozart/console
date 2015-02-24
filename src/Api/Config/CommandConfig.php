<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Config;

use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\NoSuchCommandException;
use Webmozart\Console\Assert\Assert;

/**
 * The configuration of a console command.
 *
 * There are two different ways of creating a command configuration:
 *
 *  * Call {@link create()} or {@link ApplicationConfig::beginCommand()} and use
 *    the fluent interface:
 *
 *    ```php
 *    $config = CommandConfig::create()
 *        ->setName('server')
 *        ->setDescription('List and manage servers')
 *
 *        ->beginSubCommand('add')
 *            ->setDescription('Add a new server')
 *            ->addArgument('host', InputArgument::REQUIRED)
 *            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, null, 80)
 *        ->end()
 *
 *        // ...
 *    ;
 *    ```
 *
 *  * Extend the class and implement the {@link configure()} method:
 *
 *    ```php
 *    class ServerCommandConfig extends CommandConfig
 *    {
 *        protected function configure()
 *        {
 *            $this
 *                ->setName('server')
 *                ->setDescription('List and manage servers')
 *
 *                ->beginSubCommand('add')
 *                    ->setDescription('Add a new server')
 *                    ->addArgument('host', InputArgument::REQUIRED)
 *                    ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, null, 80)
 *                ->end()
 *
 *                // ...
 *            ;
 *        }
 *    }
 *    ```
 *
 * You can choose between two different ways of executing a command:
 *
 *  * You can register a callback with {@link setCallback()}. The callback
 *    receives the input, the standard output and the error output as
 *    arguments:
 *
 *    ```php
 *    $config->setCallback(
 *        function (InputInterface $input, OutputInterface $output, OutputInterface $errorOutput) {
 *            // ...
 *        }
 *    );
 *    ```
 *
 *  * You can implement a custom command handler and return the handler from
 *    {@link getHandler()}. Since the command handler is separated, it can be
 *    easily tested:
 *
 *    ```php
 *    class ServerConfig extends CommandConfig
 *    {
 *        public function getHandler()
 *        {
 *            return new ServerHandler();
 *        }
 *    }
 *    ```
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandConfig extends Config
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ApplicationConfig
     */
    private $applicationConfig;

    /**
     * @var string[]
     */
    private $aliases = array();

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $help;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var string
     */
    private $processTitle;

    /**
     * @var SubCommandConfig[]
     */
    private $defaultCommands = array();

    /**
     * @var SubCommandConfig[]
     */
    private $subCommandConfigs = array();

    /**
     * @var OptionCommandConfig[]
     */
    private $optionCommandConfigs = array();

    /**
     * Creates a new configuration.
     *
     * @param string            $name              The name of the command.
     * @param ApplicationConfig $applicationConfig The application configuration.
     *
     * @return static The created configuration.
     */
    public static function create($name = null, ApplicationConfig $applicationConfig = null)
    {
        return new static($name, $applicationConfig);
    }

    /**
     * Creates a new configuration.
     *
     * @param string            $name              The name of the command.
     * @param ApplicationConfig $applicationConfig The application configuration.
     */
    public function __construct($name = null, ApplicationConfig $applicationConfig = null)
    {
        $this->applicationConfig = $applicationConfig;

        parent::__construct();

        if ($name) {
            $this->setName($name);
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
     * Sets the name of the command.
     *
     * @param string $name The name of the command.
     *
     * @return static The current instance.
     */
    public function setName($name)
    {
        if (null !== $name) {
            Assert::string($name, 'The command name must be a string or null. Got: %s');
            Assert::notEmpty($name, 'The command name must not be empty.');
            Assert::regex($name, '~^[a-zA-Z0-9\-]+$~', 'The command name should contain letters, digits and hyphens only. Got: %s');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Returns the application configuration.
     *
     * @return ApplicationConfig The application configuration.
     */
    public function getApplicationConfig()
    {
        return $this->applicationConfig;
    }

    /**
     * Sets the application configuration.
     *
     * @param ApplicationConfig $applicationConfig The application configuration.
     */
    public function setApplicationConfig($applicationConfig)
    {
        $this->applicationConfig = $applicationConfig;
    }

    /**
     * Ends the block when dynamically configuring a command configuration.
     *
     * This method is usually used together with
     * {@link ApplicationConfig::beginCommand()}:
     *
     * ```php
     * $config
     *     ->beginCommand('command')
     *         // ...
     *     ->end()
     *
     *     // ...
     * ;
     * ```
     *
     * @return ApplicationConfig The application configuration.
     */
    public function end()
    {
        return $this->applicationConfig;
    }

    /**
     * Returns the alias names of the command.
     *
     * @return string[] An array of alias names of the command.
     *
     * @see addAlias(), setAliases()
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Adds an alias name.
     *
     * An alias is an alternative name that can be used when calling the
     * command. Aliases are a useful way for migrating a command from one name
     * to another.
     *
     * Existing alias names are preserved.
     *
     * @param string $alias The alias name to add.
     *
     * @return static The current instance.
     *
     * @see addAliases(), setAliases(), getAlias()
     */
    public function addAlias($alias)
    {
        Assert::string($alias, 'The command alias must be a string. Got: %s');
        Assert::notEmpty($alias, 'The command alias must not be empty.');
        Assert::regex($alias, '~^[a-zA-Z0-9\-]+$~', 'The command alias should contain letters, digits and hyphens only. Got: %s');

        $this->aliases[] = $alias;

        return $this;
    }

    /**
     * Adds a list of alias names.
     *
     * Existing alias names are preserved.
     *
     * @param array $aliases The alias names to add.
     *
     * @return static The current instance.
     *
     * @see addAlias(), setAliases(), getAlias()
     */
    public function addAliases(array $aliases)
    {
        foreach ($aliases as $alias) {
            $this->addAlias($alias);
        }

        return $this;
    }

    /**
     * Sets the alias names of the command.
     *
     * Existing alias names are replaced.
     *
     * @param array $aliases The alias names.
     *
     * @return static The current instance.
     *
     * @see addAlias(), addAliases(), getAlias()
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = array();

        $this->addAliases($aliases);

        return $this;
    }

    /**
     * Returns the description of the command.
     *
     * @return string The description of the command.
     *
     * @see setDescription()
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description of the command.
     *
     * The description is a short one-liner that describes the command in the
     * command listing. The description should be written in imperative form
     * rather than in descriptive form. So:
     *
     * > List the contents of a directory.
     *
     * should be preferred over
     *
     * > Lists the contents of a directory.
     *
     * @param string $description The description.
     *
     * @return static The current instance.
     *
     * @see getDescription()
     */
    public function setDescription($description)
    {
        if (null !== $description) {
            Assert::string($description, 'The command description must be a string or null. Got: %s');
            Assert::notEmpty($description, 'The command description must not be empty.');
        }

        $this->description = $description;

        return $this;
    }

    /**
     * Returns the help text of the command.
     *
     * The help text provides additional information about a command that is
     * displayed in the help view.
     *
     * @return string The help text of the command.
     *
     * @see setHelp()
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Sets the help text of the command.
     *
     * The help text provides additional information about a command that is
     * displayed in the help view.
     *
     * @param string $help The help text of the command.
     *
     * @return static The current instance.
     *
     * @see getHelp()
     */
    public function setHelp($help)
    {
        if (null !== $help) {
            Assert::string($help, 'The help text must be a string or null. Got: %s');
            Assert::notEmpty($help, 'The help text must not be empty.');
        }

        $this->help = $help;

        return $this;
    }

    /**
     * Returns whether the command is enabled or not in the current environment.
     *
     * @return bool Returns `true` if the command is currently enabled and
     *              `false` otherwise.
     *
     * @see enable(), disable(), enableIf(), disableIf()
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Enables the command.
     *
     * @return static The current instance.
     *
     * @see enableIf(), disable(), isEnabled()
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * Enables the command if a condition holds and disables it otherwise.
     *
     * @param bool $condition The condition under which to enable the command.
     *
     * @return static The current instance.
     *
     * @see enable(), disable(), isEnabled()
     */
    public function enableIf($condition)
    {
        $this->enabled = (bool) $condition;

        return $this;
    }

    /**
     * Disables the command.
     *
     * @return static The current instance.
     *
     * @see disableIf(), enable(), isEnabled()
     */
    public function disable()
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * Disables the command if a condition holds and enables it otherwise.
     *
     * @param bool $condition The condition under which to disable the command.
     *
     * @return static The current instance.
     *
     * @see disable(), enable(), isEnabled()
     */
    public function disableIf($condition)
    {
        $this->enabled = !$condition;

        return $this;
    }

    /**
     * Returns the title of the command process.
     *
     * @return string|null The process title or `null` if no title should be
     *                     set.
     *
     * @see setProcessTitle()
     */
    public function getProcessTitle()
    {
        return $this->processTitle;
    }

    /**
     * Sets the title of the command process.
     *
     * @param string|null $processTitle The process title or `null` if no title
     *                                  should be set.
     *
     * @return static The current instance.
     *
     * @see getProcessTitle()
     */
    public function setProcessTitle($processTitle)
    {
        if (null !== $processTitle) {
            Assert::string($processTitle, 'The command process title must be a string or null. Got: %s');
            Assert::notEmpty($processTitle, 'The command process title must not be empty.');
        }

        $this->processTitle = $processTitle;

        return $this;
    }

    /**
     * Starts a configuration block for a sub-command.
     *
     * A sub-command is executed if the name of the command is passed after the
     * name of the containing command. For example, if the command "server" has
     * a sub-command command named "add", that command can be called with:
     *
     * ```
     * $ console server add ...
     * ```
     *
     * The configuration of the sub-command is returned by this method. You can
     * use the fluent interface to configure the sub-command before jumping back
     * to this configuration with {@link SubCommandConfig::end()}:
     *
     * ```php
     * protected function configure()
     * {
     *     $this
     *         ->beginCommand('server')
     *             ->setDescription('List and manage servers')
     *
     *             ->beginSubCommand('add')
     *                 ->setDescription('Add a server')
     *                 ->addArgument('host', InputArgument::REQUIRED)
     *                 ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, null, 80)
     *             ->end()
     *         ->end()
     *
     *         // ...
     *     ;
     * }
     * ```
     *
     * @param string $name The name of the sub-command.
     *
     * @return SubCommandConfig The sub-command configuration.
     *
     * @see editSubCommand()
     */
    public function beginSubCommand($name)
    {
        $config = new SubCommandConfig($name, $this);

        // The name is dynamic, so don't store by name
        $this->subCommandConfigs[] = $config;

        return $config;
    }

    /**
     * Alias for {@link getSubCommandConfig()}.
     *
     * This method can be used to nicely edit a sub-command inherited from a
     * parent configuration using the fluent API:
     *
     * ```php
     * protected function configure()
     * {
     *     parent::configure();
     *
     *     $this
     *         ->editCommand('server')
     *             ->editSubCommand('add')
     *                 // ...
     *             ->end()
     *         ->end()
     *
     *         // ...
     *     ;
     * }
     * ```
     *
     * @param string $name The name of the sub-command to edit.
     *
     * @return SubCommandConfig The sub-command configuration.
     *
     * @see beginSubCommand()
     */
    public function editSubCommand($name)
    {
        return $this->getSubCommandConfig($name);
    }

    /**
     * Adds configuration for a sub-command.
     *
     * @param SubCommandConfig $config The sub-command configuration.
     *
     * @return static The current instance.
     *
     * @see beginSubCommand()
     */
    public function addSubCommandConfig(SubCommandConfig $config)
    {
        // The name is dynamic, so don't store by name
        $this->subCommandConfigs[] = $config;

        $config->setParentConfig($this);

        return $this;
    }

    /**
     * Adds sub-command configurations to the command.
     *
     * @param SubCommandConfig[] $configs The sub-command configurations.
     *
     * @return static The current instance.
     *
     * @see beginSubCommand()
     */
    public function addSubCommandConfigs(array $configs)
    {
        foreach ($configs as $command) {
            $this->addSubCommandConfig($command);
        }

        return $this;
    }

    /**
     * Sets the sub-command configurations of the command.
     *
     * @param SubCommandConfig[] $configs The sub-command configurations.
     *
     * @return static The current instance.
     *
     * @see beginSubCommand()
     */
    public function setSubCommandConfigs(array $configs)
    {
        $this->subCommandConfigs = array();

        $this->addSubCommandConfigs($configs);

        return $this;
    }

    /**
     * Returns the sub-command configuration for a given name.
     *
     * @param string $name The name of the sub-command.
     *
     * @return SubCommandConfig The sub-command configuration.
     *
     * @throws NoSuchCommandException If the sub-command configuration is not
     *                                found.
     *
     * @see beginSubCommand()
     */
    public function getSubCommandConfig($name)
    {
        foreach ($this->subCommandConfigs as $commandConfig) {
            if ($name === $commandConfig->getName()) {
                return $commandConfig;
            }
        }

        throw NoSuchCommandException::forCommandName($name);
    }

    /**
     * Returns the configurations of all sub-commands.
     *
     * @return SubCommandConfig[] The sub-command configurations.
     *
     * @see beginSubCommand()
     */
    public function getSubCommandConfigs()
    {
        return $this->subCommandConfigs;
    }

    /**
     * Returns whether the command has a sub-command with a given name.
     *
     * @param string $name The name of the sub-command.
     *
     * @return bool Returns `true` if the sub-command configuration with the
     *              given name exists and `false` otherwise.
     *
     * @see beginSubCommand()
     */
    public function hasSubCommandConfig($name)
    {
        foreach ($this->subCommandConfigs as $commandConfig) {
            if ($name === $commandConfig->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the command has any registered sub-command configurations.
     *
     * @return bool Returns `true` if sub-command configurations were added to
     *              the command and `false` otherwise.
     *
     * @see beginSubCommand()
     */
    public function hasSubCommandConfigs()
    {
        return count($this->subCommandConfigs) > 0;
    }

    /**
     * Starts a configuration block for an option command.
     *
     * An option command is executed if the corresponding option is passed after
     * the command name. For example, if the command "server" has an option
     * command named "--add" with the short name "-a", that command can be
     * called with:
     *
     * ```
     * $ console server --add ...
     * $ console server -a ...
     * ```
     *
     * The configuration of the option command is returned by this method.
     * You can use the fluent interface to configure the option command
     * before jumping back to this configuration with
     * {@link SubCommandConfig::end()}:
     *
     * ```php
     * protected function configure()
     * {
     *     $this
     *         ->beginCommand('server')
     *             ->setDescription('List and manage servers')
     *
     *             ->beginOptionCommand('add', 'a')
     *                 ->setDescription('Add a server')
     *                 ->addArgument('host', InputArgument::REQUIRED)
     *                 ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, null, 80)
     *             ->end()
     *         ->end()
     *
     *         // ...
     *     ;
     * }
     * ```
     *
     * @param string $name      The name of the option command.
     * @param string $shortName The short name of the option command.
     *
     * @return OptionCommandConfig The option command configuration.
     *
     * @see editOptionCommand()
     */
    public function beginOptionCommand($name, $shortName = null)
    {
        $config = new OptionCommandConfig($name, $shortName, $this);

        // The name is dynamic, so don't store by name
        $this->optionCommandConfigs[] = $config;

        return $config;
    }

    /**
     * Alias for {@link getOptionCommandConfig()}.
     *
     * This method can be used to nicely edit an option command inherited from a
     * parent configuration using the fluent API:
     *
     * ```php
     * protected function configure()
     * {
     *     parent::configure();
     *
     *     $this
     *         ->editCommand('server')
     *             ->editOptionCommand('add')
     *                 // ...
     *             ->end()
     *         ->end()
     *
     *         // ...
     *     ;
     * }
     * ```
     *
     * @param string $name The name of the option command to edit.
     *
     * @return OptionCommandConfig The option command configuration.
     *
     * @see beginOptionCommand()
     */
    public function editOptionCommand($name)
    {
        return $this->getOptionCommandConfig($name);
    }

    /**
     * Adds configuration for an option command.
     *
     * @param OptionCommandConfig $config The option command configuration.
     *
     * @return static The current instance.
     *
     * @see beginOptionCommand()
     */
    public function addOptionCommandConfig(OptionCommandConfig $config)
    {
        // The name is dynamic, so don't store by name
        $this->optionCommandConfigs[] = $config;

        $config->setParentConfig($this);

        return $this;
    }

    /**
     * Adds option command configurations to the command.
     *
     * @param OptionCommandConfig[] $configs The option command configurations.
     *
     * @return static The current instance.
     *
     * @see beginOptionCommand()
     */
    public function addOptionCommandConfigs(array $configs)
    {
        foreach ($configs as $command) {
            $this->addOptionCommandConfig($command);
        }

        return $this;
    }

    /**
     * Sets the option command configurations of the command.
     *
     * @param OptionCommandConfig[] $configs The option command configurations.
     *
     * @return static The current instance.
     *
     * @see beginOptionCommand()
     */
    public function setOptionCommandConfigs(array $configs)
    {
        $this->optionCommandConfigs = array();

        $this->addOptionCommandConfigs($configs);

        return $this;
    }

    /**
     * Returns the option command configuration for a given name.
     *
     * @param string $name The long or short name of the option command.
     *
     * @return OptionCommandConfig The option command configuration.
     *
     * @throws NoSuchCommandException If the option command configuration is not
     *                                found.
     *
     * @see beginOptionCommand()
     */
    public function getOptionCommandConfig($name)
    {
        foreach ($this->optionCommandConfigs as $commandConfig) {
            if ($name === $commandConfig->getLongName() || $name === $commandConfig->getShortName()) {
                return $commandConfig;
            }
        }

        throw NoSuchCommandException::forCommandName($name);
    }

    /**
     * Returns the configurations of all option commands.
     *
     * @return OptionCommandConfig[] The option command configurations indexed
     *                               by their names.
     *
     * @see beginOptionCommand()
     */
    public function getOptionCommandConfigs()
    {
        return $this->optionCommandConfigs;
    }

    /**
     * Returns whether the command has a option command with a given name.
     *
     * @param string $name The long or short name of the option command.
     *
     * @return bool Returns `true` if the option command configuration with the
     *              given name exists and `false` otherwise.
     *
     * @see beginOptionCommand()
     */
    public function hasOptionCommandConfig($name)
    {
        foreach ($this->optionCommandConfigs as $commandConfig) {
            if ($name === $commandConfig->getLongName() || $name === $commandConfig->getShortName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the command has any registered option command configurations.
     *
     * @return bool Returns `true` if option command configurations were added to
     *              the command and `false` otherwise.
     *
     * @see beginOptionCommand()
     */
    public function hasOptionCommandConfigs()
    {
        return count($this->optionCommandConfigs) > 0;
    }

    /**
     * Starts a configuration block for a default sub-command.
     *
     * A default sub-command is executed if neither a named sub-command nor an
     * option command is executed. For example, if the command "server" has a
     * default sub-command, that command can be called with:
     *
     * ```
     * $ console server ...
     * ```
     *
     * The configuration of the sub-command is returned by this method. You can
     * use the fluent interface to configure the sub-command before jumping back
     * to this configuration with {@link SubCommandConfig::end()}:
     *
     * ```php
     * protected function configure()
     * {
     *     $this
     *         ->setName('server')
     *         ->setDescription('List and manage servers')
     *
     *         ->beginDefaultCommand()
     *             ->setDescription('List all servers')
     *             ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Only list servers with that port')
     *         ->end()
     *
     *         // ...
     *     ;
     * }
     * ```
     *
     * @return SubCommandConfig The sub-command configuration.
     */
    public function beginDefaultCommand()
    {
        $config = new SubCommandConfig(null, $this);

        $this->defaultCommands[] = $config;

        return $config;
    }

    /**
     * Adds configuration for a default sub-command.
     *
     * @param string|SubCommandConfig $nameOrConfig The default command name or
     *                                              configuration.
     *
     * @return static The current instance.
     *
     * @see beginDefaultCommand()
     */
    public function addDefaultCommand($nameOrConfig)
    {
        if ($nameOrConfig instanceof SubCommandConfig) {
            $nameOrConfig->setParentConfig($this);
        } else {
            Assert::string($nameOrConfig, 'The default command must be a string or a SubCommandConfig instance. Got: %s');
            Assert::notEmpty($nameOrConfig, 'The default command must not be empty.');
        }

        $this->defaultCommands[] = $nameOrConfig;

        return $this;
    }

    /**
     * Adds default sub-command configurations to the command.
     *
     * @param string[]|SubCommandConfig[] $namesOrConfigs The command names or
     *                                                    configurations.
     *
     * @return static The current instance.
     *
     * @see beginDefaultCommand()
     */
    public function addDefaultCommands(array $namesOrConfigs)
    {
        foreach ($namesOrConfigs as $nameOrConfig) {
            $this->addDefaultCommand($nameOrConfig);
        }

        return $this;
    }

    /**
     * Sets the default sub-commands of the command.
     *
     * @param string[]|SubCommandConfig[] $namesOrConfigs The command names or
     *                                                    configurations.
     *
     * @return static The current instance.
     *
     * @see beginDefaultCommand()
     */
    public function setDefaultCommands(array $namesOrConfigs)
    {
        $this->defaultCommands = array();

        $this->addDefaultCommands($namesOrConfigs);

        return $this;
    }

    /**
     * Returns the configurations of all default sub-commands.
     *
     * @return SubCommandConfig[] The configurations of the default sub-commands.
     *
     * @see beginDefaultCommand()
     */
    public function getDefaultCommands()
    {
        return $this->defaultCommands;
    }

    /**
     * Returns whether the command has any registered default sub-command
     * configurations.
     *
     * @return bool Returns `true` if default sun-command configurations were
     *              added to the command and `false` otherwise.
     *
     * @see beginDefaultCommand()
     */
    public function hasDefaultCommands()
    {
        return count($this->defaultCommands) > 0;
    }

    /**
     * Returns whether the given command is a default command.
     *
     * @param string $commandName The command name.
     *
     * @return bool Returns `true` if the command is in the list of default
     *              commands and `false` otherwise.
     */
    public function isDefaultCommand($commandName)
    {
        Assert::string($commandName, 'The command name must be a string. Got: %s');
        Assert::notEmpty($commandName, 'The command name must not be empty.');

        return in_array($commandName, $this->defaultCommands, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHelperSet()
    {
        return $this->applicationConfig
            ? $this->applicationConfig->getHelperSet()
            : parent::getDefaultHelperSet();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultStyleSet()
    {
        return $this->applicationConfig
            ? $this->applicationConfig->getStyleSet()
            : parent::getDefaultStyleSet();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHandler()
    {
        return $this->applicationConfig
            ? $this->applicationConfig->getHandler()
            : parent::getDefaultHandler();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHandlerMethod()
    {
        return $this->applicationConfig
            ? $this->applicationConfig->getHandlerMethod()
            : parent::getDefaultHandlerMethod();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultArgsParser()
    {
        return $this->applicationConfig
            ? $this->applicationConfig->getArgsParser()
            : parent::getDefaultArgsParser();
    }
}
