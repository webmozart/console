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

/**
 * The configuration of an console sub-command.
 *
 * A sub-command is defined within the scope of another command. For example,
 * in the command `server add <host>`, the command "add" is a sub-command of the
 * "server" command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see    OptionCommandConfig
 */
class SubCommandConfig extends CommandConfig
{
    /**
     * @var CommandConfig
     */
    private $parentConfig;

    /**
     * Creates a new configuration.
     *
     * @param string        $name         The name of the command.
     * @param CommandConfig $parentConfig The command configuration that
     *                                    contains this configuration.
     */
    public function __construct($name = null, CommandConfig $parentConfig = null)
    {
        parent::__construct($name);

        if ($parentConfig) {
            $this->setParentConfig($parentConfig);
        }
    }

    /**
     * Returns the parent command configuration.
     *
     * @return CommandConfig The parent command configuration.
     */
    public function getParentConfig()
    {
        return $this->parentConfig;
    }

    /**
     * Sets the parent command configuration.
     *
     * @param CommandConfig $parentConfig The parent command configuration.
     */
    public function setParentConfig(CommandConfig $parentConfig)
    {
        $this->parentConfig = $parentConfig;

        if ($parentConfig->getApplicationConfig()) {
            $this->setApplicationConfig($parentConfig->getApplicationConfig());
        }
    }

    /**
     * Ends the block when dynamically configuring a nested configuration.
     *
     * This method is usually used together with
     * {@link CommandConfig::beginSubCommand()},
     * {@link CommandConfig::beginOptionCommand()} or
     * {@link CommandConfig::beginDefaultCommand()}:
     *
     * ```php
     * $config
     *     ->beginSubCommand('add')
     *         // ...
     *     ->end()
     *
     *     // ...
     * ;
     * ```
     *
     * @return CommandConfig|SubCommandConfig|OptionCommandConfig The parent command configuration.
     */
    public function end()
    {
        return $this->parentConfig;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHelperSet()
    {
        return $this->parentConfig
            ? $this->parentConfig->getHelperSet()
            : parent::getDefaultHelperSet();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHandler()
    {
        return $this->parentConfig
            ? $this->parentConfig->getHandler()
            : parent::getDefaultHandler();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHandlerMethod()
    {
        return $this->parentConfig
            ? $this->parentConfig->getHandlerMethod()
            : parent::getDefaultHandlerMethod();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultArgsParser()
    {
        return $this->parentConfig
            ? $this->parentConfig->getArgsParser()
            : parent::getDefaultArgsParser();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultLenientArgsParsing()
    {
        return $this->parentConfig
            ? $this->parentConfig->isLenientArgsParsingEnabled()
            : parent::getDefaultLenientArgsParsing();
    }
}
