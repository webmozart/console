<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Args;

use Webmozart\Console\Api\Args\Format\ArgsFormat;

/**
 * The parsed console arguments.
 *
 * The parsed arguments provide access to the options and arguments passed via
 * the command line. Usually you can construct an {@link Args} instance by
 * parsing a {@link RawArgs} instance with an {@link ArgsParser}:
 *
 * ```php
 * $format = ArgsFormat::build()
 *     ->addCommandName(new CommandName('server'))
 *     ->addCommandName(new CommandName('add'))
 *     ->addOption(new Option('port', 'p', Option::VALUE_REQUIRED | Option::INTEGER))
 *     ->addArgument(new Argument('host', Argument::REQUIRED))
 *     ->getFormat();
 *
 * $args = $parser->parseArgs($rawArgs, $format);
 * ```
 *
 * The {@link ArgsFormat} defines which rules the console arguments must adhere
 * to.
 *
 * You can also create {@link Args} instances manually. This is especially
 * useful in tests:
 *
 * ```php
 * $format = ArgsFormat::build()
 *     ->addOption(new Option('port', 'p', Option::VALUE_REQUIRED | Option::INTEGER))
 *     ->addArgument(new Argument('host', Argument::REQUIRED))
 *     ->getFormat();
 *
 * $args = new Args($format);
 * $args->setOption('port', 80);
 * $args->setArgument('host', 'localhost');
 * ```
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see    RawArgs, ArgsFormat, ArgsParser
 */
class Args
{
    /**
     * @var ArgsFormat
     */
    private $format;

    /**
     * @var RawArgs
     */
    private $rawArgs;

    /**
     * @var array
     */
    private $options = array();

    /**
     * @var array
     */
    private $arguments = array();

    /**
     * Creates the console arguments.
     *
     * @param ArgsFormat $format  The format that the arguments and options must
     *                            adhere to.
     * @param RawArgs    $rawArgs The raw console arguments.
     */
    public function __construct(ArgsFormat $format, RawArgs $rawArgs = null)
    {
        $this->format = $format;
        $this->rawArgs = $rawArgs;
    }

    /**
     * Returns the command names as array.
     *
     * @return string[] The command names.
     *
     * @see CommandName
     */
    public function getCommandNames()
    {
        $names = array();

        foreach ($this->format->getCommandNames() as $commandName) {
            $names[] = $commandName->toString();
        }

        return $names;
    }

    /**
     * Returns the command options as array.
     *
     * @return string[] The command options.
     *
     * @see CommandOption
     */
    public function getCommandOptions()
    {
        $optionNames = array();

        foreach ($this->format->getCommandOptions() as $commandOption) {
            $optionNames[] = $commandOption->getLongName();
        }

        return $optionNames;
    }

    /**
     * Returns an option.
     *
     * If the option accepts a value, the value set for that option is returned.
     * If no value was set with {@link setOption()}, the default value of the
     * option is returned.j
     *
     * If the option accepts no value, the method returns `true` if the option
     * was set and `false` otherwise.
     *
     * @param string $name The long or short option name.
     *
     * @return mixed The option value or `true`/`false` for options without
     *               values.
     *
     * @throws NoSuchOptionException If the option does not exist.
     */
    public function getOption($name)
    {
        $option = $this->format->getOption($name);

        if (array_key_exists($option->getLongName(), $this->options)) {
            return $this->options[$option->getLongName()];
        }

        if ($option->acceptsValue()) {
            return $option->getDefaultValue();
        }

        return false;
    }

    /**
     * Returns all options.
     *
     * By default, this method also includes the default values set for options
     * with values. You can disable this behavior by passing `false` for
     * `$includeDefaults`.
     *
     * @param bool $includeDefaults Whether to return the default values for
     *                              options that were not set.
     *
     * @return array The option values and `true`/`false` for options without
     *               values.
     *
     * @see getOption()
     */
    public function getOptions($includeDefaults = true)
    {
        $options = $this->options;

        if ($includeDefaults) {
            foreach ($this->format->getOptions() as $option) {
                $name = $option->getLongName();

                if (!array_key_exists($name, $options)) {
                    $options[$name] = $option->acceptsValue() ? $option->getDefaultValue() : false;
                }
            }
        }

        return $options;
    }

    /**
     * Sets an option.
     *
     * For options with values, you can pass the value in the second argument.
     * The value is converted to the type defined by the argument format.
     *
     * For options without values, you can omit the second argument. Optionally,
     * you can pass `true`/`false` explicitly to enable/disable the option.
     *
     * @param string $name  The long or short option name.
     * @param mixed  $value The value to set for the option.
     *
     * @return static The current instance.
     *
     * @throws NoSuchOptionException If the option does not exist.
     */
    public function setOption($name, $value = true)
    {
        $option = $this->format->getOption($name);

        if ($option->isMultiValued()) {
            $value = (array) $value;

            foreach ($value as $k => $v) {
                $value[$k] = $option->parseValue($v);
            }
        } elseif ($option->acceptsValue()) {
            $value = $option->parseValue($value);
        } elseif (false === $value) {
            unset($this->options[$option->getLongName()]);

            return $this;
        } else {
            $value = true;
        }

        $this->options[$option->getLongName()] = $value;

        return $this;
    }

    /**
     * Sets the values of multiple options.
     *
     * The existing options are preserved.
     *
     * @param array $options The options indexed by their long or short names
     *                       and their values.
     *
     * @return static The current instance.
     *
     * @see setOption()
     */
    public function addOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Sets the values of multiple options.
     *
     * The existing options are unset.
     *
     * @param array $options The options indexed by their long or short names
     *                       and their values.
     *
     * @return static The current instance.
     *
     * @see setOption()
     */
    public function setOptions(array $options)
    {
        $this->options = array();

        $this->addOptions($options);

        return $this;
    }

    /**
     * Returns whether an option is set.
     *
     * @param string $name The long or short option name.
     *
     * @return bool Returns `true` if the option is set and `false` otherwise.
     */
    public function isOptionSet($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Returns whether an option is defined in the format.
     *
     * @param string $name The long or short option name.
     *
     * @return bool Returns `true` if the option exists and `false` otherwise.
     */
    public function isOptionDefined($name)
    {
        return $this->format->hasOption($name);
    }

    /**
     * Returns the value of an argument.
     *
     * If the argument is not set, the default value configured in the argument
     * format is returned.
     *
     * @param string|int $name The argument name or its 0-based position in the
     *                         argument list.
     *
     * @return mixed The value of the argument.
     *
     * @throws NoSuchArgumentException If the argument does not exist.
     */
    public function getArgument($name)
    {
        $argument = $this->format->getArgument($name);

        if (array_key_exists($argument->getName(), $this->arguments)) {
            return $this->arguments[$argument->getName()];
        }

        return $argument->getDefaultValue();
    }

    /**
     * Returns the values of all arguments.
     *
     * By default, this method also includes the default values of unset
     * arguments. You can disable this behavior by passing `false` for
     * `$includeDefaults`.
     *
     * @param bool $includeDefaults Whether to return the default values for
     *                              arguments that were not set.
     *
     * @return array The argument values.
     *
     * @see getArgument()
     */
    public function getArguments($includeDefaults = true)
    {
        $arguments = array();

        foreach ($this->format->getArguments() as $argument) {
            $name = $argument->getName();

            if (array_key_exists($name, $this->arguments)) {
                $arguments[$name] = $this->arguments[$name];
            } elseif ($includeDefaults) {
                $arguments[$name] = $argument->getDefaultValue();
            }
        }

        return $arguments;
    }

    /**
     * Sets the value of an argument.
     *
     * The value is converted to the type defined by the argument format.
     *
     * @param string|int $name  The argument name or its 0-based position in the
     *                          argument list.
     * @param mixed      $value The value of the argument.
     *
     * @return static The current instance.
     *
     * @throws NoSuchArgumentException If the argument does not exist.
     */
    public function setArgument($name, $value)
    {
        $argument = $this->format->getArgument($name);

        if ($argument->isMultiValued()) {
            $value = (array) $value;

            foreach ($value as $k => $v) {
                $value[$k] = $argument->parseValue($v);
            }
        } else {
            $value = $argument->parseValue($value);
        }

        $this->arguments[$argument->getName()] = $value;

        return $this;
    }

    /**
     * Sets the values of multiple arguments.
     *
     * The existing arguments are preserved.
     *
     * @param array $arguments The argument values indexed by the argument names
     *                         or their 0-based positions in the argument list.
     *
     * @return static The current instance.
     *
     * @see setArgument()
     */
    public function addArguments(array $arguments)
    {
        foreach ($arguments as $name => $value) {
            $this->setArgument($name, $value);
        }

        return $this;
    }

    /**
     * Sets the values of multiple arguments.
     *
     * The existing arguments are unset.
     *
     * @param array $arguments The argument values indexed by the argument names
     *                         or their 0-based positions in the argument list.
     *
     * @return static The current instance.
     *
     * @see setArgument()
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = array();

        $this->addArguments($arguments);

        return $this;
    }

    /**
     * Returns whether an argument is set.
     *
     * @param string|int $name The argument name or its 0-based position in the
     *                         argument list.
     *
     * @return bool Returns `true` if the argument is set and `false` otherwise.
     */
    public function isArgumentSet($name)
    {
        return array_key_exists($name, $this->arguments);
    }


    /**
     * Returns whether an argument is defined in the format.
     *
     * @param string|int $name The argument name or its 0-based position in the
     *                         argument list.
     *
     * @return bool Returns `true` if the argument exists and `false` otherwise.
     */
    public function isArgumentDefined($name)
    {
        return $this->format->hasArgument($name);
    }

    /**
     * Returns the format of the console arguments.
     *
     * @return ArgsFormat The format.
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Returns the raw console arguments.
     *
     * @return RawArgs The raw arguments.
     */
    public function getRawArgs()
    {
        return $this->rawArgs;
    }
}
