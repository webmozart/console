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

use InvalidArgumentException;
use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Args\NoSuchArgumentException;
use Webmozart\Console\Api\Args\NoSuchOptionException;

/**
 * The format used to parse a {@link RawArgs} instance.
 *
 * This class is a container for {@link CommandName}, {@link CommandOption},
 * {@link Option} and {@link Argument} objects. The format is used to interpret
 * a given {@link RawArgs} instance.
 *
 * You can pass the options and arguments to the constructor of the class:
 *
 * ```php
 * $format = new ArgsFormat(array(
 *     new CommandName('server'),
 *     new CommandName('add'),
 *     new Argument('host', Argument::REQUIRED),
 *     new Option('port', 'p', Option::VALUE_OPTIONAL, null, 80),
 * ));
 * ```
 *
 * The previous example configures a command that can be called like this:
 *
 * ```
 * $ console server add localhost
 * $ console server add localhost --port 8080
 * ```
 *
 * If the "add" command should be called via an option, change the format to:
 *
 * ```php
 * $format = new ArgsFormat(array(
 *     new CommandName('server'),
 *     new CommandOption('add', 'a'),
 *     new Argument('host', Argument::REQUIRED),
 *     new Option('port', 'p', Option::VALUE_OPTIONAL, null, 80),
 * ));
 * ```
 *
 * The command is then called like this:
 *
 * ```
 * $ console server --add localhost
 * $ console server --add localhost --port 8080
 * ```
 *
 * The format is immutable after its construction. This is necessary to maintain
 * consistency when one format inherits from another. For example, adding a
 * required argument to the base format of a format that already contains
 * optional arguments is an illegal operation that cannot be prevented if the
 * formats are mutable.
 *
 * If you want to create a format stepwisely, use an {@link ArgsFormatBuilder}.
 *
 * If multiple formats share a common set of options and arguments, extract
 * these options and arguments into a base format and let the other formats
 * inherit from this base format:
 *
 * ```php
 * $baseFormat = new ArgsFormat(array(
 *     new Option('verbose', 'v'),
 * ));
 *
 * $format = new ArgsFormat(array(
 *     new CommandName('server'),
 *     new CommandName('add'),
 *     new Argument('host', Argument::REQUIRED),
 *     new Option('port', 'p', Option::VALUE_OPTIONAL, null, 80),
 * ), $baseFormat);
 * ```
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgsFormat
{
    /**
     * @var ArgsFormat
     */
    private $baseFormat;

    /**
     * @var CommandName[]
     */
    private $commandNames;

    /**
     * @var CommandOption[]
     */
    private $commandOptions = array();

    /**
     * @var CommandOption[]
     */
    private $commandOptionsByShortName = array();

    /**
     * @var Argument[]
     */
    private $arguments;

    /**
     * @var Option[]
     */
    private $options;

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
     * Returns a format builder.
     *
     * You can optionally pass a base format. The built format inherits all the
     * arguments and options defined in the base format.
     *
     * @param ArgsFormat $baseFormat The base format.
     *
     * @return ArgsFormatBuilder The created builder.
     */
    public static function build(ArgsFormat $baseFormat = null)
    {
        return new ArgsFormatBuilder($baseFormat);
    }

    /**
     * Creates a new format.
     *
     * You can optionally pass a base format. The created format inherits all
     * the arguments and options defined in the base format.
     *
     * @param array|ArgsFormatBuilder $elements   The arguments and options or a
     *                                            builder instance.
     * @param ArgsFormat              $baseFormat The format.
     */
    public function __construct($elements = array(), ArgsFormat $baseFormat = null)
    {
        if ($elements instanceof ArgsFormatBuilder) {
            $builder = $elements;
        } else {
            $builder = $this->createBuilderForElements($elements, $baseFormat);
        }

        if (null === $baseFormat) {
            $baseFormat = $builder->getBaseFormat();
        }

        $this->baseFormat = $baseFormat;
        $this->commandNames = $builder->getCommandNames(false);
        $this->arguments = $builder->getArguments(false);
        $this->options = $builder->getOptions(false);
        $this->hasMultiValuedArg = $builder->hasMultiValuedArgument(false);
        $this->hasOptionalArg = $builder->hasOptionalArgument(false);

        foreach ($this->options as $option) {
            if ($option->getShortName()) {
                $this->optionsByShortName[$option->getShortName()] = $option;
            }
        }

        foreach ($builder->getCommandOptions(false) as $commandOption) {
            $this->commandOptions[$commandOption->getLongName()] = $commandOption;

            if ($commandOption->getShortName()) {
                $this->commandOptionsByShortName[$commandOption->getShortName()] = $commandOption;
            }

            foreach ($commandOption->getLongAliases() as $longAlias) {
                $this->commandOptions[$longAlias] = $commandOption;
            }

            foreach ($commandOption->getShortAliases() as $shortAlias) {
                $this->commandOptionsByShortName[$shortAlias] = $commandOption;
            }
        }
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
     * Returns the command names.
     *
     * @param bool $includeBase Whether to include command names in the base
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
     * Returns whether the format contains any command names.
     *
     * @param bool $includeBase Whether to consider command names in the base
     *                          format.
     *
     * @return bool Returns `true` if the format contains command names and
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
     * Returns a command option by its long or short name.
     *
     * @param string $name        The long or short option name.
     * @param bool   $includeBase Whether to include options in the base format
     *                            in the search.
     *
     * @return CommandOption The command option.
     *
     * @throws NoSuchOptionException If the command option with the given name
     *                               does not not exist.
     */
    public function getCommandOption($name, $includeBase = true)
    {
        Assert::string($name, 'The option name must be a string or an integer. Got: %s');
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
     * Returns all command options of the format.
     *
     * @param bool $includeBase Whether to include options of the base format
     *                          in the result.
     *
     * @return CommandOption[] The command options.
     */
    public function getCommandOptions($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        $commandOptions = array_values($this->commandOptions);

        if ($includeBase && $this->baseFormat) {
            // prepend base command options
            $commandOptions = array_merge($this->baseFormat->getCommandOptions(), $commandOptions);
        }

        return $commandOptions;
    }

    /**
     * Returns whether the format contains a specific command option.
     *
     * You can either pass the long or the short name of the command option.
     *
     * @param string $name        The long or short option name.
     * @param bool   $includeBase Whether to include options in the base format
     *                            in the search.
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
     * Returns whether the format contains command options.
     *
     * @param bool $includeBase Whether to include options in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the format contains command options and
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
            Assert::string($name, 'The argument name must be a string or an integer. Got: %s');
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
     * Returns all arguments of the format.
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
     * Returns whether the format contains a specific argument.
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
     * Returns whether the format contains a multi-valued argument.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the format contains a multi-valued
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
     * Returns whether the format contains an optional argument.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the format contains an optional argument
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
     * Returns whether the format contains a required argument.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the format contains a required argument
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
     * Returns whether the format contains any argument.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the format contains any argument and
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
     * Returns the number of arguments.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the result.
     *
     * @return int The number of arguments.
     */
    public function getNumberOfArguments($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        if ($this->hasMultiValuedArg) {
            return PHP_INT_MAX;
        }

        return count($this->getArguments($includeBase));
    }

    /**
     * Returns the number of required arguments.
     *
     * @param bool $includeBase Whether to include arguments in the base format
     *                          in the result.
     *
     * @return int The number of required arguments.
     */
    public function getNumberOfRequiredArguments($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase must be a boolean. Got: %s');

        $arguments = $this->getArguments($includeBase);
        $count = 0;

        foreach ($arguments as $argument) {
            if (!$argument->isRequired()) {
                continue;
            }

            if ($argument->isMultiValued()) {
                return PHP_INT_MAX;
            }

            ++$count;
        }

        return $count;
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
        Assert::string($name, 'The option name must be a string or an integer. Got: %s');
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
     * Returns all options of the format.
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
     * Returns whether the format contains a specific option.
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
     * Returns whether the format contains options.
     *
     * @param bool $includeBase Whether to include options in the base format
     *                          in the search.
     *
     * @return bool Returns `true` if the format contains options and `false`
     *              otherwise.
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
     * Creates a format builder for a set of arguments and options.
     *
     * @param array      $elements   The arguments and options to add to the
     *                               builder.
     * @param ArgsFormat $baseFormat The base format.
     *
     * @return ArgsFormatBuilder The created builder.
     */
    private function createBuilderForElements(array $elements, ArgsFormat $baseFormat = null)
    {
        $builder = new ArgsFormatBuilder($baseFormat);

        foreach ($elements as $element) {
            if ($element instanceof CommandName) {
                $builder->addCommandName($element);
            } elseif ($element instanceof CommandOption) {
                $builder->addCommandOption($element);
            } elseif ($element instanceof Option) {
                $builder->addOption($element);
            } elseif ($element instanceof Argument) {
                $builder->addArgument($element);
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Expected instances of CommandName, CommandOption, '.
                    'Option or Argument. Got: %s',
                    is_object($element) ? get_class($element) : gettype($element)
                ));
            }
        }

        return $builder;
    }
}
