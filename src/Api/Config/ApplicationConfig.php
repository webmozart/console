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

use OutOfBoundsException;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputDefinitionBuilder;
use Webmozart\Console\Api\Input\InputOption;
use Webmozart\Console\Api\Output\Dimensions;
use Webmozart\Console\Api\Resolver\CommandResolver;
use Webmozart\Console\Api\Style\StyleSet;
use Webmozart\Console\Resolver\DefaultResolver;

/**
 * The configuration of a console application.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ApplicationConfig
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $executableName;

    /**
     * @var CommandConfig[]
     */
    private $commandConfigs = array();

    /**
     * @var Dimensions
     */
    private $outputDimensions;

    /**
     * @var StyleSet
     */
    private $styleSet;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var HelperSet
     */
    private $helperSet;

    /**
     * @var InputDefinitionBuilder
     */
    private $definitionBuilder;

    /**
     * @var bool
     */
    private $catchExceptions = true;

    /**
     * @var bool
     */
    private $terminateAfterRun = true;

    /**
     * @var CommandResolver
     */
    private $commandResolver;

    public static function create($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        return new static($name, $version);
    }

    /**
     * Creates a new console application.
     *
     * @param string $name    The name of the application.
     * @param string $version The application version.
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->name = $name;
        $this->version = $version;
        $this->outputDimensions = Dimensions::forCurrentWindow();
        $this->commandResolver = new DefaultResolver('help');
        $this->definitionBuilder = new InputDefinitionBuilder();
        $this->helperSet = new HelperSet();

        $this->configure();
    }

    /**
     * Returns the name of the application.
     *
     * @return string The application name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the application.
     *
     * @param string $name The application name.
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the version of the application.
     *
     * @return string The application version.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the version of the application.
     *
     * @param string $version The application version.
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Returns the name of the application's executable.
     *
     * If no name was set via {@link setExecutableName()}, the lowercase name
     * of the application is returned.
     *
     * @return string The name of the executable.
     *
     * @see setExecutableName()
     */
    public function getExecutableName()
    {
        return $this->executableName ?: strtolower($this->name);
    }

    /**
     * Sets the name of the application's executable.
     *
     * @param string $executableName The name of the executable.
     *
     * @return static The current instance.
     *
     * @see getExecutableName()
     */
    public function setExecutableName($executableName)
    {
        $this->executableName = $executableName;

        return $this;
    }

    /**
     * Returns the event dispatcher used to dispatch the console events.
     *
     * @return EventDispatcherInterface The event dispatcher.
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Sets the event dispatcher for dispatching the console events.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @return static The current instance.
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Returns the helper set used by the application.
     *
     * @return HelperSet The helper set.
     *
     * @see setHelperSet()
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * Sets the helper set used by the application.
     *
     * @param HelperSet $helperSet The helper set.
     *
     * @return static The current instance.
     *
     * @see getHelperSet()
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;

        return $this;
    }

    /**
     * Returns whether the application catches and displays exceptions thrown
     * while running a command.
     *
     * @return boolean Returns `true` if exceptions are caught and `false`
     *                 otherwise.
     *
     * @see setCatchExceptions()
     */
    public function isExceptionCaught()
    {
        return $this->catchExceptions;
    }

    /**
     * Sets whether the application catches and displays exceptions thrown
     * while running a command.
     *
     * @param boolean $catch Whether to catch and display exceptions thrown
     *                       while running a command.
     *
     * @return static The current instance.
     *
     * @see isExceptionCaught()
     */
    public function setCatchExceptions($catch)
    {
        $this->catchExceptions = (bool) $catch;

        return $this;
    }

    /**
     * Returns whether the PHP process is terminated after running a command.
     *
     * @return boolean Returns `true` if the PHP process is terminated after
     *                 {@link run()} and `false` otherwise.
     *
     * @see setTerminateAfterRun()
     */
    public function isTerminatedAfterRun()
    {
        return $this->terminateAfterRun;
    }

    /**
     * Sets whether to terminate the PHP process after running a command.
     *
     * @param boolean $terminate Whether to terminate the PHP process after
     *                           running a command.
     *
     * @return static The current instance.
     *
     * @see isTerminatedAfterRun()
     */
    public function setTerminateAfterRun($terminate)
    {
        $this->terminateAfterRun = (bool) $terminate;

        return $this;
    }

    /**
     * Returns the dimensions of the output window.
     *
     * @return Dimensions The output dimensions.
     *
     * @see setOutputDimensions()
     */
    public function getOutputDimensions()
    {
        return $this->outputDimensions;
    }

    /**
     * Sets the dimensions of the output window.
     *
     * @param Dimensions $dimensions The output dimensions.
     *
     * @return static The current instance.
     *
     * @see getOutputDimensions()
     */
    public function setOutputDimensions(Dimensions $dimensions)
    {
        $this->outputDimensions = $dimensions;

        return $this;
    }

    /**
     * Returns the used style set.
     *
     * @return StyleSet The style set.
     *
     * @see setStyleSet()
     */
    public function getStyleSet()
    {
        return $this->styleSet;
    }

    /**
     * Sets the used style set.
     *
     * @param StyleSet $styleSet The style set to use.
     *
     * @return static The current instance.
     *
     * @see getStyleSet()
     */
    public function setStyleSet(StyleSet $styleSet)
    {
        $this->styleSet = $styleSet;

        return $this;
    }

    /**
     * Returns the used command resolver.
     *
     * @return CommandResolver The command resolver.
     *
     * @see setCommandResolver()
     */
    public function getCommandResolver()
    {
        return $this->commandResolver;
    }

    /**
     * Sets the used command resolver.
     *
     * @param CommandResolver $commandResolver The command resolver.
     *
     * @return static The current instance.
     *
     * @see getCommandResolver()
     */
    public function setCommandResolver(CommandResolver $commandResolver)
    {
        $this->commandResolver = $commandResolver;

        return $this;
    }

    /**
     * Sets the default to run when no explicit command is requested.
     *
     * @param string $commandName The name of the default command.
     *
     * @return static The current instance.
     */
    public function setDefaultCommand($commandName)
    {
        $this->commandResolver = new DefaultResolver($commandName);

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
     * Adds command configurations to the application.
     *
     * @param CommandConfig[] $configs The command configurations.
     *
     * @return static The current instance.
     *
     * @see beginCommand(), getCommandConfigs()
     */
    public function addCommandConfigs(array $configs)
    {
        foreach ($configs as $command) {
            $this->addCommandConfig($command);
        }

        return $this;
    }

    /**
     * Adds a command configuration to the application.
     *
     * @param CommandConfig $config The command configuration.
     *
     * @return static The current instance.
     *
     * @see beginCommand(), getCommandConfigs()
     */
    public function addCommandConfig(CommandConfig $config)
    {
        // The name is dynamic, so don't store by name
        $this->commandConfigs[] = $config;

        return $this;
    }

    /**
     * Builds a command
     *
     * @param $name
     *
     * @return CommandConfig
     *
     * @see addCommandConfig(), getCommandConfigs()
     */
    public function beginCommand($name)
    {
        $commandConfig = new CommandConfig($name, $this);

        // The name is dynamic, so don't store by name
        $this->commandConfigs[] = $commandConfig;

        return $commandConfig;
    }

    /**
     * Returns the command configuration for a given name.
     *
     * @param string $name The name of the command.
     *
     * @return CommandConfig The command configuration.
     *
     * @throws OutOfBoundsException If the command configuration is not found.
     *
     * @see addCommandConfig(), getCommandConfigs()
     */
    public function getCommandConfig($name)
    {
        foreach ($this->commandConfigs as $commandConfig) {
            if ($name === $commandConfig->getName()) {
                return $commandConfig;
            }
        }

        throw new OutOfBoundsException(sprintf(
            'The command configuration named "%s" does not exist.',
            $name
        ));
    }

    /**
     * Returns all registered command configurations.
     *
     * @return CommandConfig[] The command configurations.
     *
     * @see addCommandConfig(), getCommandConfig()
     */
    public function getCommandConfigs()
    {
        return $this->commandConfigs;
    }

    /**
     * Returns whether the application has a command with a given name.
     *
     * @param string $name The name of the command.
     *
     * @return bool Returns `true` if the command configuration with the given
     *              name exists and `false` otherwise.
     *
     * @see hasCommandConfigs(), getCommandConfig()
     */
    public function hasCommandConfig($name)
    {
        foreach ($this->commandConfigs as $commandConfig) {
            if ($name === $commandConfig->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the application has any registered command configurations.
     *
     * @return bool Returns `true` if command configurations were added to the
     *              application and `false` otherwise.
     *
     * @see hasCommandConfig(), getCommandConfigs()
     */
    public function hasCommandConfigs()
    {
        return count($this->commandConfigs) > 0;
    }

    /**
     * Configures the application.
     *
     * Override this method in your own subclasses to configure the instance.
     */
    protected function configure()
    {
    }
}
