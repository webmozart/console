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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Console\Api\Command\NoSuchCommandException;
use Webmozart\Console\Rendering\Dimensions;
use Webmozart\Console\Api\Resolver\CommandResolver;
use Webmozart\Console\Assert\Assert;
use Webmozart\Console\Resolver\DefaultResolver;

/**
 * The configuration of a console application.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ApplicationConfig extends BaseConfig
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $help;

    /**
     * @var CommandConfig[]
     */
    private $commandConfigs = array();

    /**
     * @var CommandConfig[]
     */
    private $unnamedCommandConfigs = array();

    /**
     * @var \Webmozart\Console\Rendering\Dimensions
     */
    private $outputDimensions;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

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

    /**
     * Creates a new console application.
     *
     * @param string $name    The name of the application.
     * @param string $version The application version.
     *
     * @return static The created instance.
     */
    public static function create($name = null, $version = null)
    {
        return new static($name, $version);
    }

    /**
     * Creates a new console application.
     *
     * @param string $name    The name of the application.
     * @param string $version The application version.
     */
    public function __construct($name = null, $version = null)
    {
        $this->name = $name;
        $this->version = $version;
        $this->outputDimensions = Dimensions::forCurrentWindow();

        parent::__construct();
    }

    /**
     * Returns the name of the application.
     *
     * @return string The application name.
     *
     * @see setName()
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
     * @return static The current instance.
     *
     * @see getName()
     */
    public function setName($name)
    {
        if (null !== $name) {
            Assert::string($name, 'The application name must be a string. Got: %s');
            Assert::notEmpty($name, 'The application name must not be empty.');
            Assert::regex($name, '~^[a-zA-Z0-9\-]+$~', 'The application name must contain letters, numbers and hyphens only. Did you mean to call setDisplayName()?');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Returns the application name as it is displayed in the help.
     *
     * If no display name is set with {@link setDisplayName()}, the humanized
     * application name is returned.
     *
     * @return string The display name.
     *
     * @see setDisplayName()
     */
    public function getDisplayName()
    {
        return $this->displayName ?: $this->getDefaultDisplayName();
    }

    /**
     * Sets the application name as it is displayed in the help.
     *
     * @param string $displayName The display name.
     *
     * @return static The current instance.
     *
     * @see getDisplayName()
     */
    public function setDisplayName($displayName)
    {
        if (null !== $displayName) {
            Assert::string($displayName, 'The display name must be a string. Got: %s');
            Assert::notEmpty($displayName, 'The display name must not be empty.');
        }

        $this->displayName = $displayName;

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
     * @return static The current instance.
     */
    public function setVersion($version)
    {
        if (null !== $version) {
            Assert::string($version, 'The application version must be a string. Got: %s');
            Assert::notEmpty($version, 'The application version must not be empty.');
        }

        $this->version = $version;

        return $this;
    }

    /**
     * Returns the help text of the application.
     *
     * @return string The help text.
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Sets the help text of the application.
     *
     * @param string $help The help text.
     *
     * @return static The current instance.
     */
    public function setHelp($help)
    {
        if (null !== $help) {
            Assert::string($help, 'The help text must be a string. Got: %s');
            Assert::notEmpty($help, 'The help text must not be empty.');
        }

        $this->help = $help;

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
        Assert::boolean($catch);

        $this->catchExceptions = $catch;

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
        Assert::boolean($terminate);

        $this->terminateAfterRun = $terminate;

        return $this;
    }

    /**
     * Returns the dimensions of the output window.
     *
     * @return \Webmozart\Console\Rendering\Dimensions The output dimensions.
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
     * @param \Webmozart\Console\Rendering\Dimensions $dimensions The output dimensions.
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
     * Returns the used command resolver.
     *
     * @return CommandResolver The command resolver.
     *
     * @see setCommandResolver()
     */
    public function getCommandResolver()
    {
        if (!$this->commandResolver) {
            $this->commandResolver = new DefaultResolver();
        }

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
     * Starts a configuration block for a command.
     *
     * The configuration of the command is returned by this method. You can use
     * the fluent interface to configure the sub-command before jumping back to
     * this configuration with {@link CommandConfig::end()}:
     *
     * ```php
     * protected function configure()
     * {
     *     $this
     *         ->setName('server')
     *         ->setDescription('List and manage servers')
     *
     *         ->beginCommand('add')
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
     * @param string $name The name of the command.
     *
     * @return CommandConfig The command configuration.
     */
    public function beginCommand($name)
    {
        $commandConfig = new CommandConfig($name, $this);

        // The name is dynamic, so don't store by name
        $this->commandConfigs[] = $commandConfig;

        return $commandConfig;
    }

    /**
     * Adds a command configuration to the application.
     *
     * @param CommandConfig $config The command configuration.
     *
     * @return static The current instance.
     *
     * @see beginCommand()
     */
    public function addCommandConfig(CommandConfig $config)
    {
        // The name is dynamic, so don't store by name
        $this->commandConfigs[] = $config;

        return $this;
    }

    /**
     * Adds command configurations to the application.
     *
     * @param CommandConfig[] $configs The command configurations.
     *
     * @return static The current instance.
     *
     * @see beginCommand()
     */
    public function addCommandConfigs(array $configs)
    {
        foreach ($configs as $command) {
            $this->addCommandConfig($command);
        }

        return $this;
    }

    /**
     * Sets the command configurations of the application.
     *
     * @param CommandConfig[] $configs The command configurations.
     *
     * @return static The current instance.
     *
     * @see beginCommand()
     */
    public function setCommandConfigs(array $configs)
    {
        $this->commandConfigs = array();

        $this->addCommandConfigs($configs);

        return $this;
    }

    /**
     * Returns the command configuration for a given name.
     *
     * @param string $name The name of the command.
     *
     * @return CommandConfig The command configuration.
     *
     * @throws NoSuchCommandException If the command configuration is not found.
     *
     * @see beginCommand()
     */
    public function getCommandConfig($name)
    {
        foreach ($this->commandConfigs as $commandConfig) {
            if ($name === $commandConfig->getName()) {
                return $commandConfig;
            }
        }

        throw NoSuchCommandException::forCommandName($name);
    }

    /**
     * Returns all registered command configurations.
     *
     * @return CommandConfig[] The command configurations.
     *
     * @see beginCommand()
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
     * @see beginCommand()
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
     * @see beginCommand()
     */
    public function hasCommandConfigs()
    {
        return count($this->commandConfigs) > 0;
    }

    /**
     * Starts a configuration block for an unnamed command.
     *
     * The configuration of the command is returned by this method. You can use
     * the fluent interface to configure the command before jumping back to this
     * configuration with {@link CommandConfig::end()}:
     *
     * ```php
     * protected function configure()
     * {
     *     $this
     *         ->setName('server')
     *
     *         ->beginUnnamedCommand()
     *             ->setDescription('List all servers')
     *             ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Only list servers with that port')
     *         ->end()
     *
     *         // ...
     *     ;
     * }
     * ```
     *
     * An unnamed command is executed if no named command is explicitly
     * requested. The above command could be called with:
     *
     * ```
     * $ server -p 80
     * ```
     *
     * @return CommandConfig The command configuration.
     */
    public function beginUnnamedCommand()
    {
        $config = new CommandConfig(null, $this);

        $this->unnamedCommandConfigs[] = $config;

        return $config;
    }

    /**
     * Adds configuration for an unnamed command.
     *
     * @param CommandConfig $config The command configuration.
     *
     * @return static The current instance.
     *
     * @see beginUnnamedCommand()
     */
    public function addUnnamedCommandConfig(CommandConfig $config)
    {
        $this->unnamedCommandConfigs[] = $config;

        $config->setApplicationConfig($this);

        return $this;
    }

    /**
     * Adds configurations for unnamed commands.
     *
     * @param CommandConfig[] $configs The command configurations.
     *
     * @return static The current instance.
     *
     * @see beginUnnamedCommand()
     */
    public function addUnnamedCommandConfigs(array $configs)
    {
        foreach ($configs as $command) {
            $this->addUnnamedCommandConfig($command);
        }

        return $this;
    }

    /**
     * Sets the unnamed command configurations of the application.
     *
     * @param CommandConfig[] $configs The command configurations.
     *
     * @return static The current instance.
     *
     * @see beginUnnamedCommand()
     */
    public function setUnnamedCommandConfigs(array $configs)
    {
        $this->unnamedCommandConfigs = array();

        $this->addUnnamedCommandConfigs($configs);

        return $this;
    }

    /**
     * Returns the configurations of all unnamed commands.
     *
     * @return CommandConfig[] The configurations of the unnamed commands.
     *
     * @see beginUnnamedCommand()
     */
    public function getUnnamedCommandConfigs()
    {
        return $this->unnamedCommandConfigs;
    }

    /**
     * Returns whether the application has any registered unnamed command
     * configurations.
     *
     * @return bool Returns `true` if unnamed command configurations were added
     *              to the application and `false` otherwise.
     *
     * @see beginUnnamedCommand()
     */
    public function hasUnnamedCommandConfigs()
    {
        return count($this->unnamedCommandConfigs) > 0;
    }

    /**
     * Returns the default display name used if no display name is set.
     *
     * @return string The default display name.
     */
    protected function getDefaultDisplayName()
    {
        if (!$this->name) {
            return null;
        }

        return ucwords(preg_replace('~[\s-_]+~', ' ', $this->name));
    }
}
