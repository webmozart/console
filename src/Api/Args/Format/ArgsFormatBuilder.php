<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Args\Format;

use LogicException;
use Webmozart\Console\Api\Args\NoSuchArgumentException;
use Webmozart\Console\Api\Args\NoSuchOptionException;
use Webmozart\Console\Assert\Assert;

/**
 * A builder for {@link ArgsFormat} instances.
 *
 * Use the methods in this class to dynamically build {@link ArgsFormat}
 * instances. When you are done configuring the builder, call
 * {@link getFormat()} to build an immutable {@link ArgsFormat}.
 *
 * For convenience, you can call {@link ArgsFormat::build()} to create a new
 * builder and use its fluent API to configure and build a format:
 *
 * ```php
 * $format = ArgsFormat::build()
 *     ->addCommandName(new CommandName('server'))
 *     ->addCommandOption(new CommandOption('add', 'a'))
 *     ->addArgument(new Argument('host'))
 *     ->addOption(new Option('port', 'p'))
 *     ->getFormat();
 * ```
 *
 * You can optionally pass a base format to inherit from. The arguments of the
 * base format are prepended to the arguments of the built format. The options
 * of the base format are added to the built options:
 *
 * ```php
 * $baseFormat = ArgsFormat::build()
 *     ->addOption(new Option('verbose', 'v'))
 *     ->getFormat();
 *
 * $format = ArgsFormat::build($baseFormat)
 *     // ...
 *     ->getFormat();
 * ```
 *
 * Read {@link ArgsFormat} for a more detailed description of args formats.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see    ArgsFormat
 */
class ArgsFormatBuilder
{
    /**
     * @var ArgsFormat
     */
    private $baseFormat;

    /**
     * @var CommandName[]
     */
    private $commandNames = array();

    /**
     * @var Option[]
     */
    private $commandOptions = array();

    /**
     * @var Option[]
     */
    private $commandOptionsByShortName = array();

    /**
     * @var Argument[]
     */
    private $arguments = array();

    /**
     * @var Option[]
     */
    private $options = array();

    /**
     * @var Option[]
     */
    private $optionsByShortName = array();

    /**
     * @var bool
     */
    private $hasMultiValuedArg = false;

    /**
     * @var bool
     */
    private $hasOptionalArg = false;

    /**
     * Creates a new builder.
     *
     * You can optionally pass a base format. The built format inherits all the
     * arguments and options from the base format.
     *
     * @param ArgsFormat $baseFormat The base format.
     */
    public function __construct(ArgsFormat $baseFormat = null)
    {
        $this->baseFormat = $baseFormat;
    }

    /**
     * Returns the base format.
     *
     * @return ArgsFormat The base format.
     */
    public function getBaseFormat()
    {
        return $this->baseFormat;
    }

    /**
     * Sets the command names of the built format.
     *
     * @param CommandName[] $commandNames The command names.
     *
     * @return static The current instance.
     */
    public function setCommandNames(array $commandNames)
    {
        $this->commandNames = array();

        $this->addCommandNames($commandNames);

        return $this;
    }

    /**
     * Adds command names to the built format.
     *
     * @param CommandName[] $commandNames The command names to add.
     *
     * @return static The current instance.
     */
    public function addCommandNames(array $commandNames)
    {
        foreach ($commandNames as $commandName) {
            $this->addCommandName($commandName);
        }

        return $this;
    }

    /**
     * Adds a command name to the built format.
     *
     * @param CommandName $commandName The command name to add.
     *
     * @return static The current instance.
     */
    public function addCommandName(CommandName $commandName)
    {
        $this->commandNames[] = $commandName;

        return $this;
    }

    /**
     * Returns whether the builder contains any command names.
     *
     * @param bool $includeBase Whether to consider command names of the base
     *                          format.
     *
     * @return bool Returns `true` if the builder contains any command names and
     *              `false` otherwise.
     */
    public function hasCommandNames($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (count($this->commandNames) > 0) {
            return true;
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->hasCommandNames();
        }

        return false;
    }

    /**
     * Returns all command names added to the builder.
     *
     * @param bool $includeBase Whether to include command names of the base
     *                          format in the result.
     *
     * @return CommandName[] The command names.
     */
    public function getCommandNames($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        $commandNames = $this->commandNames;

        if ($includeBase && $this->baseFormat) {
            $commandNames = array_merge($this->baseFormat->getCommandNames(), $commandNames);
        }

        return $commandNames;
    }

    /**
     * Sets the command options of the built format.
     *
     * Any existing command options are removed when this method is called.
     *
     * @param CommandOption[] $commandOptions The command options of the built
     *                                        format.
     *
     * @return static The current instance.
     *
     * @throws LogicException If an incorrect command option is given.
     *
     * @see addCommandOption()
     */
    public function setCommandOptions(array $commandOptions)
    {
        $this->commandOptions = array();
        $this->commandOptionsByShortName = array();

        $this->addCommandOptions($commandOptions);

        return $this;
    }

    /**
     * Adds command options to the builder.
     *
     * The existing command options stored in the builder are preserved.
     *
     * @param CommandOption[] $commandOptions The command options to add.
     *
     * @return static The current instance.
     *
     * @throws LogicException If an incorrect command option is given.
     *
     * @see addCommandOption()
     */
    public function addCommandOptions(array $commandOptions)
    {
        foreach ($commandOptions as $commandOption) {
            $this->addCommandOption($commandOption);
        }

        return $this;
    }

    /**
     * Adds a command option to the builder.
     *
     * The existing command options stored in the builder are preserved.
     *
     * @param CommandOption $commandOption The command option to add.
     *
     * @return static The current instance.
     *
     * @throws LogicException If an incorrect command option is given.
     *
     * @see addCommandOptions()
     */
    public function addCommandOption(CommandOption $commandOption)
    {
        $longName = $commandOption->getLongName();
        $shortName = $commandOption->getShortName();

        if (isset($this->commandOptions[$longName]) || isset($this->options[$longName])) {
            throw new LogicException(sprintf('An option named "--%s" exists already.', $longName));
        }

        if (isset($this->commandOptionsByShortName[$shortName]) || isset($this->optionsByShortName[$shortName])) {
            throw new LogicException(sprintf('An option named "-%s" exists already.', $shortName));
        }

        $this->commandOptions[$longName] = $commandOption;

        if ($shortName) {
            $this->commandOptionsByShortName[$shortName] = $commandOption;
        }

        return $this;
    }

    /**
     * Returns whether the builder contains a specific command option.
     *
     * You can either pass the long or the short name of the command option.
     *
     * @param string $name        The long or short option name.
     * @param bool   $includeBase Whether to include command options in the base
     *                            format in the search.
     *
     * @return bool Returns `true` if the command option with the given name
     *              could be found and `false` otherwise.
     */
    public function hasCommandOption($name, $includeBase = true)
    {
        Assert::string($name, 'The option name must be a string or an integer. Got: %s');
        Assert::notEmpty($name, 'The option name must not be empty.');
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (isset($this->commandOptions[$name]) || isset($this->commandOptionsByShortName[$name])) {
            return true;
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->hasCommandOption($name);
        }

        return false;
    }

    /**
     * Returns whether the builder contains any command options.
     *
     * @param bool $includeBase Whether to include command  options in the base
     *                          format in the search.
     *
     * @return bool Returns `true` if the builder contains command options and
     *              `false` otherwise.
     */
    public function hasCommandOptions($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (count($this->commandOptions) > 0) {
            return true;
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->hasCommandOptions();
        }

        return false;
    }

    /**
     * Returns a command option by its long or short name.
     *
     * @param string $name        The long or short option name.
     * @param bool   $includeBase Whether to include command options in the base
     *                            format in the search.
     *
     * @return CommandOption The command option.
     *
     * @throws NoSuchOptionException If the command  option with the given name
     *                               does not not exist.
     */
    public function getCommandOption($name, $includeBase = true)
    {
        Assert::string($name, 'The option name must be a string. Got: %s');
        Assert::notEmpty($name, 'The option name must not be empty.');
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (isset($this->commandOptions[$name])) {
            return $this->commandOptions[$name];
        }

        if (isset($this->commandOptionsByShortName[$name])) {
            return $this->commandOptionsByShortName[$name];
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->getCommandOption($name);
        }

        throw NoSuchOptionException::forOptionName($name);
    }

    /**
     * Returns all command options added to the builder.
     *
     * @param bool $includeBase Whether to include command options of the base
     *                          format in the result.
     *
     * @return CommandOption[] The command options.
     */
    public function getCommandOptions($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        $commandOptions = $this->commandOptions;

        if ($includeBase && $this->baseFormat) {
            // prepend base command options
            $commandOptions = array_replace($this->baseFormat->getCommandOptions(), $commandOptions);
        }

        return $commandOptions;
    }

    /**
     * Sets the arguments of the built format.
     *
     * Any existing arguments are removed when this method is called.
     *
     * @param Argument[] $arguments The arguments of the built format.
     *
     * @return static The current instance.
     *
     * @throws LogicException If an incorrect argument is given.
     *
     * @see addArgument()
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = array();
        $this->hasOptionalArg = false;
        $this->hasMultiValuedArg = false;

        $this->addArguments($arguments);

        return $this;
    }

    /**
     * Adds arguments at the end of the argument list.
     *
     * The existing arguments stored in the builder are preserved.
     *
     * @param Argument[] $arguments The arguments to add.
     *
     * @return static The current instance.
     *
     * @throws LogicException If an incorrect argument is given.
     *
     * @see addArgument()
     */
    public function addArguments(array $arguments)
    {
        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }

        return $this;
    }

    /**
     * Adds an argument at the end of the argument list.
     *
     * The existing arguments stored in the builder are preserved.
     *
     * You cannot add arguments after adding a multi-valued argument. If you do
     * so, this method throws an exception.
     *
     * Adding required arguments after optional arguments is not supported.
     * Also in this case an exception is thrown.
     *
     * @param Argument $argument The argument to add.
     *
     * @return static The current instance.
     *
     * @throws LogicException If an incorrect argument is given.
     */
    public function addArgument(Argument $argument)
    {
        $name = $argument->getName();

        if ($this->hasArgument($name)) {
            throw new LogicException(sprintf('An argument with the name "%s" exists already.', $name));
        }

        if ($this->hasMultiValuedArgument()) {
            throw new LogicException('Cannot add an argument after a multi-valued argument.');
        }

        if ($argument->isRequired() && $this->hasOptionalArgument()) {
            throw new LogicException('Cannot add a required argument after an optional one.');
        }

        if ($argument->isMultiValued()) {
            $this->hasMultiValuedArg = true;
        }

        if ($argument->isOptional()) {
            $this->hasOptionalArg = true;
        }

        $this->arguments[$name] = $argument;

        return $this;
    }

    /**
     * Returns whether the builder contains a specific argument.
     *
     * You can either pass the name of the argument or the 0-based position of
     * the argument.
     *
     * @param string|int $name        The argument name or its 0-based position
     *                                in the argument list.
     * @param bool       $includeBase Whether to include arguments in the base
     *                                format in the search.
     *
     * @return bool Returns `true` if the argument with the given name or
     *              position could be found and `false` otherwise.
     */
    public function hasArgument($name, $includeBase = true)
    {
        if (!is_int($name)) {
            Assert::string($name, 'The argument name must be a string or an integer. Got: %s');
            Assert::notEmpty($name, 'The argument name must not be empty.');
        }

        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        $arguments = is_int($name)
            ? array_values($this->getArguments($includeBase))
            : $this->getArguments($includeBase);

        return isset($arguments[$name]);
    }

    /**
     * Returns whether the builder contains a multi-valued argument.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the builder contains a multi-valued
     *              argument and `false` otherwise.
     */
    public function hasMultiValuedArgument($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if ($this->hasMultiValuedArg) {
            return true;
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->hasMultiValuedArgument();
        }

        return false;
    }

    /**
     * Returns whether the builder contains an optional argument.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the builder contains an optional argument
     *              and `false` otherwise.
     */
    public function hasOptionalArgument($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if ($this->hasOptionalArg) {
            return true;
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->hasOptionalArgument();
        }

        return false;
    }

    /**
     * Returns whether the builder contains a required argument.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the builder contains a required argument
     *              and `false` otherwise.
     */
    public function hasRequiredArgument($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (!$this->hasOptionalArg && count($this->arguments) > 0) {
            return true;
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->hasRequiredArgument();
        }

        return false;
    }

    /**
     * Returns whether the builder contains any argument.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the builder contains any argument and
     *              `false` otherwise.
     */
    public function hasArguments($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (count($this->arguments) > 0) {
            return true;
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->hasArguments();
        }

        return false;
    }

    /**
     * Returns an argument by its name or position.
     *
     * You can either pass the name of the argument or the 0-based position of
     * the argument.
     *
     * @param string|int $name        The argument name or its 0-based position
     *                                in the argument list.
     * @param bool       $includeBase Whether to include arguments in the base
     *                                format in the search.
     *
     * @return Argument The argument.
     *
     * @throws NoSuchArgumentException If the argument with the given name or
     *                                 position does not exist.
     */
    public function getArgument($name, $includeBase = true)
    {
        if (!is_int($name)) {
            Assert::string($name, 'The argument name must be a string or integer. Got: %s');
            Assert::notEmpty($name, 'The argument name must not be empty.');
        }

        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (is_int($name)) {
            $arguments = array_values($this->getArguments($includeBase));

            if (!isset($arguments[$name])) {
                throw NoSuchArgumentException::forPosition($name);
            }
        } else {
            $arguments = $this->getArguments($includeBase);

            if (!isset($arguments[$name])) {
                throw NoSuchArgumentException::forArgumentName($name);
            }
        }

        return $arguments[$name];
    }

    /**
     * Returns all arguments added to the builder.
     *
     * @param bool $includeBase Whether to include arguments of the base format
     *                          in the result.
     *
     * @return Argument[] The arguments.
     */
    public function getArguments($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        $arguments = $this->arguments;

        if ($includeBase && $this->baseFormat) {
            // prepend base arguments
            $arguments = array_replace($this->baseFormat->getArguments(), $arguments);
        }

        return $arguments;
    }

    /**
     * Sets the options of the built format.
     *
     * Any existing options are removed when this method is called.
     *
     * @param Option[] $options The options of the built format.
     *
     * @return static The current instance.
     *
     * @throws LogicException If an incorrect option is given.
     *
     * @see addOption()
     */
    public function setOptions(array $options)
    {
        $this->options = array();
        $this->optionsByShortName = array();

        $this->addOptions($options);

        return $this;
    }

    /**
     * Adds options at the end of the options list.
     *
     * The existing options stored in the builder are preserved.
     *
     * @param Option[] $options The options to add.
     *
     * @return static The current instance.
     *
     * @throws LogicException If an incorrect option is given.
     *
     * @see addOption()
     */
    public function addOptions(array $options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }

        return $this;
    }

    /**
     * Adds an option at the end of the options list.
     *
     * The existing options stored in the builder are preserved.
     *
     * @param Option $option The option to add.
     *
     * @return static The current instance.
     *
     * @throws LogicException If an incorrect option is given.
     *
     * @see addOptions()
     */
    public function addOption(Option $option)
    {
        $longName = $option->getLongName();
        $shortName = $option->getShortName();

        if (isset($this->options[$longName]) || isset($this->commandOptions[$longName])) {
            throw new LogicException(sprintf('An option named "--%s" exists already.', $longName));
        }

        if (isset($this->optionsByShortName[$shortName]) || isset($this->commandOptionsByShortName[$shortName])) {
            throw new LogicException(sprintf('An option named "-%s" exists already.', $shortName));
        }

        $this->options[$longName] = $option;

        if ($shortName) {
            $this->optionsByShortName[$shortName] = $option;
        }

        return $this;
    }

    /**
     * Returns whether the builder contains a specific option.
     *
     * You can either pass the long or the short name of the option.
     *
     * @param string $name        The long or short option name.
     * @param bool   $includeBase Whether to include options in the base format
     *                            in the search.
     *
     * @return bool Returns `true` if the option with the given name could be
     *              found and `false` otherwise.
     */
    public function hasOption($name, $includeBase = true)
    {
        Assert::string($name, 'The option name must be a string or an integer. Got: %s');
        Assert::notEmpty($name, 'The option name must not be empty.');
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (isset($this->options[$name]) || isset($this->optionsByShortName[$name])) {
            return true;
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->hasOption($name);
        }

        return false;
    }

    /**
     * Returns whether the builder contains any option.
     *
     * @param bool $includeBase Whether to include options in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the builder contains any option and
     *              `false` otherwise.
     */
    public function hasOptions($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (count($this->options) > 0) {
            return true;
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->hasOptions();
        }

        return false;
    }

    /**
     * Returns an option by its long or short name.
     *
     * @param string $name        The long or short option name.
     * @param bool   $includeBase Whether to include options in the base format
     *                            in the search.
     *
     * @return Option The option.
     *
     * @throws NoSuchOptionException If the option with the given name does not
     *                               not exist.
     */
    public function getOption($name, $includeBase = true)
    {
        Assert::string($name, 'The option name must be a string. Got: %s');
        Assert::notEmpty($name, 'The option name must not be empty.');
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        if (isset($this->optionsByShortName[$name])) {
            return $this->optionsByShortName[$name];
        }

        if ($includeBase && $this->baseFormat) {
            return $this->baseFormat->getOption($name);
        }

        throw NoSuchOptionException::forOptionName($name);
    }

    /**
     * Returns all options added to the builder.
     *
     * @param bool $includeBase Whether to include options of the base format
     *                          in the result.
     *
     * @return Option[] The options.
     */
    public function getOptions($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        $options = $this->options;

        if ($includeBase && $this->baseFormat) {
            // append base options
            $options = array_replace($options, $this->baseFormat->getOptions());
        }

        return $options;
    }

    /**
     * Builds a format with the arguments and options added to the builder.
     *
     * @return ArgsFormat The built format.
     */
    public function getFormat()
    {
        return new ArgsFormat($this, $this->baseFormat);
    }
}
