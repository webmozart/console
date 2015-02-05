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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Api\Handler\CommandHandler;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Input\InputDefinitionBuilder;
use Webmozart\Console\Api\Input\InputOption;
use Webmozart\Console\Api\Runnable;
use Webmozart\Console\Assert\Assert;
use Webmozart\Console\Handler\CallableHandler;
use Webmozart\Console\Handler\RunnableHandler;

/**
 * A console command.
 *
 * A `Command` object contains all the information that is necessary to describe
 * and run a console command. The actual execution of the command is delegated
 * to a {@link CommandHandler} object returned by {@link getHandler()}.
 *
 * This class can be configured in two ways:
 *
 *  * You can create and configure `Command` objects directly:
 *
 *    ```php
 *    $command = Command::create()
 *        ->setName('ls')
 *        ->setDescription('List the contents of a directory')
 *        ->addArgument('directory', InputArgument::OPTIONAL)
 *        ->addOption('all', 'a')
 *        ->setCallback(...);
 *    ```
 *
 *  * You can extend the class and configure the command in the
 *    {@link configure()} method:
 *
 *    ```php
 *    class LsCommand extends Command
 *    {
 *        protected function configure()
 *        {
 *            $this
 *                ->setName('ls')
 *                ->setDescription('List the contents of a directory')
 *                ->addArgument('directory', InputArgument::OPTIONAL)
 *                ->addOption('all', 'a')
 *            ;
 *        }
 *    }
 *    ```
 *
 * There are three different ways of executing a command:
 *
 *  * You can register a callback with {@link setCallback()}. The callback
 *    receives the input, the standard output and the error output as
 *    arguments:
 *
 *    ```php
 *    $command->setCallback(
 *        function (InputInterface $input, OutputInterface $output, OutputInterface $errorOutput) {
 *            // ...
 *        }
 *    );
 *    ```
 *
 *  * You can extend the class and implement the {@link run()} method:
 *
 *    ```php
 *    class LsCommand extends Command
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
 *    class LsCommand extends Command
 *    {
 *        public function getHandler()
 *        {
 *            return new LsHandler();
 *        }
 *    }
 *    ```
 *
 * Command objects run through two different phases:
 *
 *  * In the configuration phase, the information stored in the command can be
 *    set and modified.
 *
 *  * Once {@link freeze()} is called, the command is frozen. The command's
 *    {@link InputDefinition} is now built and can be accessed with
 *    {@link getInputDefinition()}. A frozen command cannot be changed anymore.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Command implements Runnable
{
    /**
     * @var string
     */
    private $name;

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
     * @var string[]
     */
    private $aliases = array();

    /**
     * @var string
     */
    private $processTitle;

    /**
     * @var InputDefinition
     */
    private $inputDefinition;

    /**
     * @var InputDefinitionBuilder
     */
    private $definitionBuilder;

    /**
     * @var callable
     */
    private $callback;

    /**
     * Creates a new command.
     *
     * This method can be used as an alternative to {@link __construct()} if
     * you want to configure a command with the fluent API:
     *
     * ```php
     * $command = Command::create()
     *     ->setName('ls')
     *     ->setDescription('List the contents of a directory')
     *     ->addArgument('
     * ```
     *
     * @param null $name
     *
     * @return static
     */
    public static function create($name = null)
    {
        return new static($name);
    }

    /**
     * Creates a new command.
     *
     * @param string|null $name The command's name.
     */
    public function __construct($name = null)
    {
        $this->definitionBuilder = new InputDefinitionBuilder();

        if ($name) {
            $this->setName($name);
        }

        $this->configure();
    }

    /**
     * Freezes the command.
     *
     * This method builds the {@link InputDefinition} of the command. The
     * command cannot be modified anymore after freezing.
     *
     * @throws LogicException If the command is already frozen or if the name
     *                        has not been set yet.
     *
     * @see isFrozen()
     */
    public function freeze()
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command is already frozen.');
        }

        if (!$this->name) {
            throw new LogicException('The name must be set before freezing a command.');
        }

        $this->inputDefinition = $this->definitionBuilder->getDefinition();
        $this->definitionBuilder = null;
    }

    /**
     * Returns whether the command is frozen.
     *
     * @return bool Returns `true` if the command is frozen and `false`
     *              otherwise.
     *
     * @see freeze()
     */
    public function isFrozen()
    {
        return null === $this->definitionBuilder;
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
     *
     * @throws LogicException If the command is frozen.
     */
    public function setName($name)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

        Assert::string($name, 'The command name must be a string. Got: %s');
        Assert::notEmpty($name, 'The command name must not be empty.');
        Assert::regex($name, '~^[a-zA-Z0-9\-]+$~', 'The command name should contain letters, digits, hyphens and underscores only. Got: %s');

        $this->name = $name;

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
     * @throws LogicException If the command is frozen.
     *
     * @see getDescription()
     */
    public function setDescription($description)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

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
     * @throws LogicException If the command is frozen.
     *
     * @see getHelp()
     */
    public function setHelp($help)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

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
     * @throws LogicException If the command is frozen.
     *
     * @see enableIf(), disable(), isEnabled()
     */
    public function enable()
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

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
     * @throws LogicException If the command is frozen.
     *
     * @see enable(), disable(), isEnabled()
     */
    public function enableIf($condition)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

        $this->enabled = (bool) $condition;

        return $this;
    }

    /**
     * Disables the command.
     *
     * @return static The current instance.
     *
     * @throws LogicException If the command is frozen.
     *
     * @see disableIf(), enable(), isEnabled()
     */
    public function disable()
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

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
     * @throws LogicException If the command is frozen.
     *
     * @see disable(), enable(), isEnabled()
     */
    public function disableIf($condition)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

        $this->enabled = !$condition;

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
     * @throws LogicException If the command is frozen.
     *
     * @see addAliases(), setAliases(), getAlias()
     */
    public function addAlias($alias)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

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
     * @throws LogicException If the command is frozen.
     *
     * @see addAlias(), setAliases(), getAlias()
     */
    public function addAliases(array $aliases)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

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
     * @throws LogicException If the command is frozen.
     *
     * @see addAlias(), addAliases(), getAlias()
     */
    public function setAliases(array $aliases)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

        $this->aliases = array();

        $this->addAliases($aliases);

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
     * @throws LogicException If the command is frozen.
     *
     * @see getProcessTitle()
     */
    public function setProcessTitle($processTitle)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

        Assert::nullOrString($processTitle, 'The command process title must be a string or null. Got: %s');
        Assert::nullOrNotEmpty($processTitle, 'The command process title must not be empty.');

        $this->processTitle = $processTitle;

        return $this;
    }

    /**
     * Adds a command argument.
     *
     * Read {@link InputArgument} for a more detailed description of command
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
     * @throws LogicException If the command is frozen.
     *
     * @see addOption(), getInputDefinition(), setBaseInputDefinition()
     */
    public function addArgument($name, $flags = null, $description = '', $default = null)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

        $this->definitionBuilder->addArgument(new InputArgument($name, $flags, $description, $default));

        return $this;
    }

    /**
     * Adds a command option.
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
     * @throws LogicException If the command is frozen.
     *
     * @see addArgument(), getInputDefinition(), setBaseInputDefinition()
     */
    public function addOption($longName, $shortName = null, $flags = null, $description = '', $default = null)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

        $this->definitionBuilder->addOption(new InputOption($longName, $shortName, $flags, $description, $default));

        return $this;
    }

    /**
     * Sets the base input definition to inherit arguments and options from.
     *
     * @param InputDefinition $baseDefinition The base input definition
     *                                        containing global command
     *                                        arguments and options.
     *
     * @return static The current instance.
     *
     * @throws LogicException If the command is frozen.
     *
     * @see addArgument(), addOption(), getInputDefinition()
     */
    public function setBaseInputDefinition(InputDefinition $baseDefinition)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

        $currentArguments = $this->definitionBuilder->getArguments(false);
        $currentOptions = $this->definitionBuilder->getOptions(false);

        $this->definitionBuilder = new InputDefinitionBuilder($baseDefinition);
        $this->definitionBuilder->setArguments($currentArguments);
        $this->definitionBuilder->setOptions($currentOptions);

        return $this;
    }

    /**
     * Returns the input definition for this command.
     *
     * The command must be frozen with {@link freeze()} before accessing this
     * method.
     *
     * @return InputDefinition The input definition.
     *
     * @throws LogicException If the command has not been frozen yet.
     *
     * @see addArgument(), addOption(), setBaseInputDefinition()
     */
    public function getInputDefinition()
    {
        if (!$this->isFrozen()) {
            throw new LogicException('The command must be frozen before accessing the input definition.');
        }

        return $this->inputDefinition;
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
     * @throws LogicException If the command is frozen.
     *
     * @see run(), getHandler()
     */
    public function setCallback($callback)
    {
        if ($this->isFrozen()) {
            throw new LogicException('The command cannot be modified once it is frozen.');
        }

        Assert::isCallable($callback);

        $this->callback = $callback;

        return $this;
    }

    /**
     * Returns the command handler to execute when the command is run.
     *
     * You can set a command handler by:
     *
     *  * Passing a callable to {@link setCallback()}.
     *  * Implementing {@link run()}.
     *  * Overriding this method and returning a custom {@link CommandHandler}.
     *
     * Implementing a {@link CommandHandler} is recommended if you want to test
     * the command handler.
     *
     * @return CommandHandler The command handler.
     *
     * @see run(), setCallback()
     */
    public function getHandler()
    {
        if ($this->callback) {
            return new CallableHandler($this->callback);
        }

        return new RunnableHandler($this);
    }

    /**
     * Executes the command.
     *
     * Alternatively to implementing this method, you can pass a callback to
     * {@link setCallback()} or return a custom {@link CommandHandler}
     * implementation from {@link getHandler()}.
     *
     * @param InputInterface  $input       The console input.
     * @param OutputInterface $output      The standard output.
     * @param OutputInterface $errorOutput The error output.
     *
     * @return int Returns 0 on success and a positive integer on error.
     */
    public function run(InputInterface $input, OutputInterface $output, OutputInterface $errorOutput)
    {
        return 0;
    }

    /**
     * Configures the command.
     *
     * This method can be overridden to configure the command:
     *
     * ```php
     * class LsCommand extends Command
     * {
     *     protected function configure()
     *     {
     *         $this
     *             ->setName('ls')
     *             ->setDescription('List the contents of a directory')
     *             ->addArgument('directory', InputArgument::OPTIONAL)
     *             ->addOption('all', 'a')
     *         ;
     *     }
     * }
     * ```
     *
     * Alternatively, you can create and configure {@link Command} objects
     * directly:
     *
     * ```php
     * $command = Command::create()
     *     ->setName('ls')
     *     ->setDescription('List the contents of a directory')
     *     ->addArgument('directory', InputArgument::OPTIONAL)
     *     ->addOption('all', 'a')
     * ;
     * ```
     */
    protected function configure()
    {
    }
}
