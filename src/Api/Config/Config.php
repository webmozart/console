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
use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Args\ArgsParser;
use Webmozart\Console\Api\Args\Format\ArgsFormatBuilder;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Args\DefaultArgsParser;
use Webmozart\Console\Handler\NullHandler;

/**
 * Implements methods shared by all configurations.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class Config
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
     * @var ArgsParser
     */
    private $argsParser;

    /**
     * @var bool
     */
    private $lenientArgsParsing;

    /**
     * @var object|callable
     */
    private $handler;

    /**
     * @var string
     */
    private $handlerMethod;

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
     *                            the {@link Argument} class.
     * @param string $description A one-line description of the argument.
     * @param mixed  $default     The default value. Must be `null` if the
     *                            flags contain {@link Argument::REQUIRED}.
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
     *                            the {@link Option} class.
     * @param string $description A one-line description of the option.
     * @param mixed  $default     The default value. Must be `null` if the
     *                            flags contain {@link Option::REQUIRED_VALUE}.
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
     * Returns whether lenient argument parsing is enabled.
     *
     * When lenient argument parsing is enabled, the argument parser will not
     * fail if the console arguments contain invalid or missing arguments.
     *
     * @return boolean Returns `true` if lenient parsing is enabled and `false`
     *                 otherwise.
     */
    public function isLenientArgsParsingEnabled()
    {
        if (null === $this->lenientArgsParsing) {
            return $this->getDefaultLenientArgsParsing();
        }

        return $this->lenientArgsParsing;
    }

    /**
     * Enables lenient argument parsing.
     *
     * When lenient argument parsing is enabled, the argument parser will not
     * fail if the console arguments contain invalid or missing arguments.
     *
     * Lenient argument parsing is disabled by default.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see disableLenientArgsParsing()
     */
    public function enableLenientArgsParsing()
    {
        $this->lenientArgsParsing = true;

        return $this;
    }

    /**
     * Disables lenient argument parsing.
     *
     * When lenient argument parsing is enabled, the argument parser will not
     * fail if the console arguments contain invalid or missing arguments.
     *
     * Lenient argument parsing is disabled by default.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see enableLenientArgsParsing()
     */
    public function disableLenientArgsParsing()
    {
        $this->lenientArgsParsing = false;

        return $this;
    }

    /**
     * Returns the command handler to execute when a command is run.
     *
     * @return object The command handler.
     *
     * @see setHandler()
     */
    public function getHandler()
    {
        if (!$this->handler) {
            return $this->getDefaultHandler();
        }

        if (is_callable($this->handler)) {
            $this->handler = call_user_func($this->handler);
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
     * @see getHandler()
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
     * Returns the method of the command handler that should be executed when
     * the configured command is run.
     *
     * @return string The method name.
     *
     * @see setHandlerMethod()
     */
    public function getHandlerMethod()
    {
        if (!$this->handlerMethod) {
            return $this->getDefaultHandlerMethod();
        }

        return $this->handlerMethod;
    }

    /**
     * Sets the method of the command handler that should be executed when the
     * configured command is run.
     *
     * The method receives three arguments:
     *
     *  * {@link Args} `$args`: The console arguments.
     *  * {@link IO} `$io`: The I/O.
     *  * {@link Command} `$command`: The executed command.
     *
     * @param string $handlerMethod The method name.
     *
     * @return ApplicationConfig|CommandConfig|SubCommandConfig|OptionCommandConfig The current instance.
     *
     * @see getHandlerMethod()
     */
    public function setHandlerMethod($handlerMethod)
    {
        Assert::string($handlerMethod, 'The handler method must be a string. Got: %s');
        Assert::notEmpty($handlerMethod, 'The handler method must not be empty.');

        $this->handlerMethod = $handlerMethod;

        return $this;
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
     * Returns the command handler to use if none is set.
     *
     * @return object The default command handler.
     */
    protected function getDefaultHandler()
    {
        return new NullHandler();
    }

    /**
     * Returns the handler method to use if none is set.
     *
     * @return string The default handler method.
     */
    protected function getDefaultHandlerMethod()
    {
        return 'handle';
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
     * Returns whether the arguments parsing handles errors gracefully.
     *
     * @return bool The default value for lenient args parsing.
     */
    protected function getDefaultLenientArgsParsing()
    {
        return false;
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
