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

use InvalidArgumentException;
use Symfony\Component\Console\Helper\HelperSet;
use Webmozart\Console\Api\Args\ArgsParser;
use Webmozart\Console\Api\Args\Format\ArgsFormatBuilder;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Formatter\StyleSet;
use Webmozart\Console\Args\DefaultArgsParser;
use Webmozart\Console\Assert\Assert;
use Webmozart\Console\Formatter\DefaultStyleSet;
use Webmozart\Console\Handler\CallbackHandler;
use Webmozart\Console\Handler\NullHandler;

/**
 * Implements methods shared by all configurations.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class BaseConfig
{
    /**
     * @var ArgsFormatBuilder
     */
    private $formatBuilder;

    /**
     * @var HelperSet
     */
    private $helperSet;

    /**
     * @var StyleSet
     */
    private $styleSet;

    /**
     * @var ArgsParser
     */
    private $argsParser;

    /**
     * @var object|callable
     */
    private $handler;

    /**
     * @var string
     */
    private $handlerMethod = 'handle';

    /**
     * @var string[]
     */
    private $defaultCommands = array();

    /**
     * Creates a new configuration.
     */
    public function __construct()
    {
        $this->formatBuilder = new ArgsFormatBuilder();

        $this->configure();
    }

    /**
     * Returns the configured arguments.
     *
     * Read {@link Argument} for a more detailed description of arguments.
     *
     * @return Argument[] The configured arguments.
     *
     * @see addArgument()
     */
    public function getArguments()
    {
        return $this->formatBuilder->getArguments();
    }

    /**
     * Adds an argument.
     *
     * Read {@link Argument} for a more detailed description of console
     * arguments.
     *
     * @param string $name        The argument name.
     * @param int    $flags       A bitwise combination of the flag constants in
     *                            the {@link InputArgument} class.
     * @param string $description A one-line description of the argument.
     * @param mixed  $default     The default value. Must be `null` if the
     *                            flags contain {@link InputArgument::REQUIRED}.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see getArguments()
     */
    public function addArgument($name, $flags = 0, $description = null, $default = null)
    {
        $this->formatBuilder->addArgument(new Argument($name, $flags, $description, $default));

        return $this;
    }

    /**
     * Returns the configured options.
     *
     * Read {@link Option} for a more detailed description of console options.
     *
     * @return Option[] The configured options.
     *
     * @see addOption()
     */
    public function getOptions()
    {
        return $this->formatBuilder->getOptions();
    }

    /**
     * Adds an option.
     *
     * Read {@link Option} for a more detailed description of console options.
     *
     * @param string $longName    The long option name.
     * @param string $shortName   The short option name. Can be `null`.
     * @param int    $flags       A bitwise combination of the flag constants in
     *                            the {@link InputOption} class.
     * @param string $description A one-line description of the option.
     * @param mixed  $default     The default value. Must be `null` if the
     *                            flags contain {@link InputOption::VALUE_REQUIRED}.
     * @param string $valueName   The name of the value to be used in usage
     *                            examples of the option.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see getOptions()
     */
    public function addOption($longName, $shortName = null, $flags = 0, $description = null, $default = null, $valueName = '...')
    {
        $this->formatBuilder->addOption(new Option($longName, $shortName, $flags, $description, $default, $valueName));

        return $this;
    }

    /**
     * Returns the configured helper set.
     *
     * @return HelperSet The helper set.
     *
     * @see setHelperSet()
     */
    public function getHelperSet()
    {
        if (!$this->helperSet) {
            return $this->getDefaultHelperSet();
        }

        return $this->helperSet;
    }

    /**
     * Sets the used helper set.
     *
     * @param HelperSet $helperSet The helper set.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see getHelperSet()
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;

        return $this;
    }

    /**
     * Returns the configured style set.
     *
     * @return StyleSet The style set.
     *
     * @see setStyleSet()
     */
    public function getStyleSet()
    {
        if (!$this->styleSet) {
            return $this->getDefaultStyleSet();
        }

        return $this->styleSet;
    }

    /**
     * Sets the used style set.
     *
     * @param StyleSet $styleSet The style set to use.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see getStyleSet()
     */
    public function setStyleSet(StyleSet $styleSet)
    {
        $this->styleSet = $styleSet;

        return $this;
    }

    /**
     * Returns the configured argument parser.
     *
     * @return ArgsParser The argument parser.
     *
     * @see setArgsParser()
     */
    public function getArgsParser()
    {
        if (!$this->argsParser) {
            return $this->getDefaultArgsParser();
        }

        return $this->argsParser;
    }

    /**
     * Sets the used argument parser.
     *
     * @param ArgsParser $argsParser The argument parser.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see getArgsParser()
     */
    public function setArgsParser(ArgsParser $argsParser)
    {
        $this->argsParser = $argsParser;

        return $this;
    }

    /**
     * Returns the command handler to execute when a command is run.
     *
     * @param Command $command The command to handle.
     *
     * @return object The command handler.
     *
     * @see setHandler()
     */
    public function getHandler(Command $command)
    {
        if (!$this->handler) {
            return $this->getDefaultHandler($command);
        }

        if (is_callable($this->handler)) {
            $this->handler = call_user_func($this->handler, $command);
        }

        return $this->handler;
    }

    /**
     * Sets the command handler to execute when a command is run.
     *
     * You can pass:
     *
     *  * An object with handler methods.
     *  * A callable that receives a {@link Command} and returns an object with
     *    handler methods.
     *
     * The name of the executed handler method can be configured with
     * {@link setHandlerMethod()}. By default, the method `handle()` is
     * executed.
     *
     * @param object|callback $handler The command handler or the callable
     *                                 creating a new command handler on demand.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see setCallback(), getHandler()
     */
    public function setHandler($handler)
    {
        if (!is_object($handler) && !is_callable($handler)) {
            throw new InvalidArgumentException(sprintf(
                'Expected an object or a callable. Got: %s',
                is_object($handler) ? get_class($handler) : gettype($handler)
            ));
        }

        $this->handler = $handler;

        return $this;
    }

    /**
     * @return string
     */
    public function getHandlerMethod()
    {
        return $this->handlerMethod;
    }

    /**
     * @param string $handlerMethod
     */
    public function setHandlerMethod($handlerMethod)
    {
        Assert::string($handlerMethod, 'The handler method must be a string. Got: %s');
        Assert::notEmpty($handlerMethod, 'The handler method must not be empty.');

        $this->handlerMethod = $handlerMethod;
    }

    /**
     * Sets the callback to execute when a command is run.
     *
     * The callback receives three arguments:
     *
     *  * {@link Args} `$args`: The console arguments.
     *  * {@link Input} `$input`: The standard input.
     *  * {@link Output} `$output`: The standard output.
     *  * {@link Output} `$errorOutput`: The error output.
     *
     * The callback should return 0 on success and a positive integer on error.
     *
     * @param callable $callback The callback to execute when the command is run.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see setHandler(), getHandler()
     */
    public function setCallback($callback)
    {
        $this->setHandler(new CallbackHandler($callback));

        return $this;
    }

    /**
     * Returns the default command to run when no explicit command is requested.
     *
     * @return string[] The names of the default commands.
     *
     * @see addDefaultCommand(), setDefaultCommands()
     */
    public function getDefaultCommands()
    {
        return $this->defaultCommands;
    }

    /**
     * Adds a default command to run when no explicit command is requested.
     *
     * @param string $commandName The name of the default command.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see addDefaultCommands(), setDefaultCommands()
     */
    public function addDefaultCommand($commandName)
    {
        Assert::string($commandName, 'The default command name must be a string. Got: %s');
        Assert::notEmpty($commandName, 'The default command name must not be empty.');

        $this->defaultCommands[] = $commandName;

        return $this;
    }

    /**
     * Adds default commands to run when no explicit command is requested.
     *
     * @param string[] $commandNames The names of the default commands.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see addDefaultCommand(), setDefaultCommands()
     */
    public function addDefaultCommands(array $commandNames)
    {
        foreach ($commandNames as $commandName) {
            $this->addDefaultCommand($commandName);
        }

        return $this;
    }

    /**
     * Sets the default commands to run when no explicit command is requested.
     *
     * The resolver tries all default commands until a command is found that
     * matches the passed console arguments.
     *
     * @param string[] $commandNames The names of the default commands.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see getDefaultCommands(), addDefaultCommand()
     */
    public function setDefaultCommands(array $commandNames)
    {
        $this->defaultCommands = array();

        $this->addDefaultCommands($commandNames);

        return $this;
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
        return in_array($commandName, $this->defaultCommands, true);
    }

    /**
     * Returns whether the configuration contains default commands.
     *
     * @return bool Returns `true` if default commands are set and `false`
     *              otherwise.
     */
    public function hasDefaultCommands()
    {
        return count($this->defaultCommands) > 0;
    }

    /**
     * Returns the helper set to use if none is set.
     *
     * @return HelperSet The default helper set.
     */
    protected function getDefaultHelperSet()
    {
        return new HelperSet();
    }

    /**
     * Returns the style set to use if none is set.
     *
     * @return DefaultStyleSet The default style set.
     */
    protected function getDefaultStyleSet()
    {
        return new DefaultStyleSet();
    }

    /**
     * Returns the command handler to use if none is set.
     *
     * @param Command $command The command to handle.
     *
     * @return object The default command handler.
     */
    protected function getDefaultHandler(Command $command)
    {
        return new NullHandler();
    }

    /**
     * Returns the arguments parser to use if none is set.
     *
     * @return ArgsParser The default args parser.
     */
    protected function getDefaultArgsParser()
    {
        return new DefaultArgsParser();
    }

    /**
     * Adds the default configuration.
     *
     * Override this method in your own subclasses.
     */
    protected function configure()
    {
    }
}
