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

use Webmozart\Console\Api\Handler\CommandHandler;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputDefinitionBuilder;
use Webmozart\Console\Api\Input\InputOption;
use Webmozart\Console\Api\Runnable;
use Webmozart\Console\Assert\Assert;
use Webmozart\Console\Handler\CallableHandler;
use Webmozart\Console\Handler\NullHandler;
use Webmozart\Console\Handler\RunnableHandler;

/**
 * The configuration of a console command.
 *
 * There are two different ways of creating a command configuration:
 *
 *  * Call {@link create()} and use the fluent interface:
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
 * You can choose between three different ways of executing a command:
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
 *  * You can extend the class and implement the {@link Runnable} interface:
 *
 *    ```php
 *    class ServerCommandConfig extends CommandConfig implements Runnable
 *    {
 *        public function run(InputInterface $input, OutputInterface $output, OutputInterface $errorOutput)
 *        {
 *            // ...
 *        }
 *    }
 *    ```
 *
 *  * You can implement a custom command handler and return the handler from
 *    {@link getHandler()}. Since the command handler is separated, it can be
 *    easily tested:
 *
 *    ```php
 *    class ServerCommandConfig extends CommandConfig
 *    {
 *        public function getHandler()
 *        {
 *            return new ServerCommandHandler();
 *        }
 *    }
 *    ```
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandConfig
{
    /**
     * @var string
     */
    private $name;

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
     * @var callable
     */
    private $callback;

    /**
     * @var InputDefinitionBuilder
     */
    private $definitionBuilder;

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
     * @param string $name The name of the command.
     *
     * @return static The created configuration.
     */
    public static function create($name = null)
    {
        return new static($name);
    }

    /**
     * Creates a new configuration.
     *
     * @param string $name The name of the command.
     */
    public function __construct($name = null)
    {
        $this->definitionBuilder = new InputDefinitionBuilder();

        $this->setName($name);

        $this->configure();
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
            Assert::regex($name, '~^[a-zA-Z0-9\-]+$~', 'The command name should contain letters, digits, hyphens and underscores only. Got: %s');
            Assert::greaterThan(strlen($name), 1, sprintf('The command name should contain at least two characters. Got: "%s"', $name));
        }

        $this->name = $name;

        return $this;
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
        Assert::nullOrString($description, 'The command description must be a string or null. Got: %s');
        Assert::nullOrNotEmpty($description, 'The command description must not be empty.');

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
        Assert::nullOrString($help, 'The command help must be a string or null. Got: %s');
        Assert::nullOrNotEmpty($help, 'The command help must not be empty.');

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
        Assert::nullOrString($processTitle, 'The command process title must be a string or null. Got: %s');
        Assert::nullOrNotEmpty($processTitle, 'The command process title must not be empty.');

        $this->processTitle = $processTitle;

        return $this;
    }

    /**
     * Returns the callback to execute when the command is run.
     *
     * @return callable The callback or `null` if no callback was set.
     *
     * @see setCallback()
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Sets the callback to execute when the command is run.
     *
     * The callback receives three arguments:
     *
     *  * {@link InputInterface} `$input`: The console input.
     *  * {@link OutputInterface} `$output`: The standard output.
     *  * {@link OutputInterface} `$errorOutput`: The error output.
     *
     * The callback should return 0 on success and a positive integer on error.
     *
     * Alternatively to setting a callback, you can implement {@link run()} or
     * return a {@link CommandHandler} implementation from {@link getHandler()}.
     *
     * @param callable $callback The callback to execute when the command is run.
     *
     * @return static The current instance.
     *
     * @see getCallback(), getHandler()
     */
    public function setCallback($callback)
    {
        Assert::nullOrIsCallable($callback);

        $this->callback = $callback;

        return $this;
    }

    /**
     * Returns the input arguments of the command.
     *
     * Read {@link InputArgument} for a more detailed description of input
     * arguments.
     *
     * @return InputArgument[] The input arguments.
     *
     * @see addArgument()
     */
    public function getArguments()
    {
        return $this->definitionBuilder->getArguments();
    }

    /**
     * Adds an input argument to the command.
     *
     * Read {@link InputArgument} for a more detailed description of input
     * arguments.
     *
     * @param string $name        The argument name.
     * @param int    $flags       A bitwise combination of the flag constants in
     *                            the {@link InputArgument} class.
     * @param string $description A one-line description of the argument.
     * @param mixed  $default     The default value. Must be `null` if the
     *                            flags contain {@link InputArgument::REQUIRED}.
     *
     * @return static The current instance.
     *
     * @see getArguments(), addSubCommandConfig()
     */
    public function addArgument($name, $flags = 0, $description = null, $default = null)
    {
        $this->definitionBuilder->addArgument(new InputArgument($name, $flags, $description, $default));

        return $this;
    }

    /**
     * Returns the input options of the command.
     *
     * Read {@link InputOption} for a more detailed description of input
     * options.
     *
     * @return InputOption[] The input options.
     *
     * @see addOption()
     */
    public function getOptions()
    {
        return $this->definitionBuilder->getOptions();
    }

    /**
     * Adds an input option.
     *
     * Read {@link InputOption} for a more detailed description of command
     * arguments.
     *
     * @param string $longName    The long option name.
     * @param string $shortName   The short option name. Can be `null`.
     * @param int    $flags       A bitwise combination of the flag constants in
     *                            the {@link InputOption} class.
     * @param string $description A one-line description of the option.
     * @param mixed  $default     The default value. Must be `null` if the
     *                            flags contain {@link InputOption::VALUE_REQUIRED}.
     *
     * @return static The current instance.
     *
     * @see getOptions(), addOptionCommandConfig()
     */
    public function addOption($longName, $shortName = null, $flags = 0, $description = null, $default = null)
    {
        $this->definitionBuilder->addOption(new InputOption($longName, $shortName, $flags, $description, $default));

        return $this;
    }

    /**
     * Returns the configurations of all embedded commands.
     *
     * @return SubCommandConfig[] The sub-command configurations indexed by
     *                            their names.
     *
     * @see beginSubCommand(), addSubCommandConfig()
     */
    public function getSubCommandConfigs()
    {
        return $this->subCommandConfigs;
    }

    /**
     * Adds configuration for a sub-command.
     *
     * @param SubCommandConfig $config The sub-command configuration.
     *
     * @return static The current instance.
     *
     * @see beginSubCommand(), getSubCommandConfigs()
     */
    public function addSubCommandConfig(SubCommandConfig $config)
    {
        $this->subCommandConfigs[$config->getName()] = $config;

        $config->setParentConfig($this);

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
     *         ->setName('server')
     *         ->setDescription('List and manage servers')
     *
     *         ->beginSubCommand('add')
     *             ->setDescription('Add a server')
     *             ->addArgument('host', InputArgument::REQUIRED)
     *             ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, null, 80)
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
     * @see addSubCommandConfig(), getSubCommandConfigs()
     */
    public function beginSubCommand($name)
    {
        $config = new SubCommandConfig($name, $this);

        $this->subCommandConfigs[$name] = $config;

        return $config;
    }

    /**
     * Returns the configurations of all option commands.
     *
     * @return OptionCommandConfig[] The option command configurations indexed
     *                               by their names.
     *
     * @see beginOptionCommand(), addOptionCommandConfig()
     */
    public function getOptionCommandConfigs()
    {
        return $this->optionCommandConfigs;
    }

    /**
     * Adds configuration for an option command.
     *
     * @param OptionCommandConfig $config The option command configuration.
     *
     * @return static The current instance.
     *
     * @see beginOptionCommand(), getOptionCommandConfigs()
     */
    public function addOptionCommandConfig(OptionCommandConfig $config)
    {
        $this->optionCommandConfigs[$config->getName()] = $config;

        $config->setParentConfig($this);

        return $this;
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
     *         ->setName('server')
     *         ->setDescription('List and manage servers')
     *
     *         ->beginOptionCommand('add', 'a')
     *             ->setDescription('Add a server')
     *             ->addArgument('host', InputArgument::REQUIRED)
     *             ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, null, 80)
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
     * @see addOptionCommandConfig(), getOptionCommandConfigs()
     */
    public function beginOptionCommand($name, $shortName = null)
    {
        $config = new OptionCommandConfig($name, $shortName, $this);

        $this->optionCommandConfigs[$name] = $config;

        return $config;
    }

    /**
     * Returns the command handler to execute when the command is run.
     *
     * You can set a command handler by:
     *
     *  * Passing a callable to {@link setCallback()}.
     *  * Implementing {@link Runnable}.
     *  * Overriding this method and returning a custom {@link CommandHandler}.
     *
     * Implementing a {@link CommandHandler} is recommended if you want to test
     * the command handler.
     *
     * @param Command $command The command to handle.
     *
     * @return CommandHandler The command handler.
     *
     * @see run(), setCallback()
     */
    public function getHandler(Command $command)
    {
        if ($this->callback) {
            return new CallableHandler($this->callback);
        }

        if ($this instanceof Runnable) {
            return new RunnableHandler($this);
        }

        return new NullHandler();
    }

    /**
     * Configures the command.
     *
     * Override this method in your own subclasses to configure the instance.
     */
    protected function configure()
    {
    }
}
