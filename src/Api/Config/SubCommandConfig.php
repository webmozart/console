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

use Symfony\Component\Console\Helper\HelperSet;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Handler\CommandHandler;
use Webmozart\Console\Handler\NullHandler;

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
     * {@link CommandConfig::beginSubCommand()} or
     * {@link CommandConfig::beginOptionCommand()}:
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
     * Returns the helper set used by the command.
     *
     * @param bool $fallback Whether to return the parent helper set if none is
     *                       set.
     *
     * @return HelperSet The helper set.
     *
     * @see setHelperSet()
     */
    public function getHelperSet($fallback = true)
    {
        $actualHelperSet = parent::getHelperSet(false);

        if (!$fallback) {
            return $actualHelperSet;
        }

        if (null === $actualHelperSet && $fallback && $this->parentConfig) {
            return $this->parentConfig->getHelperSet();
        }

        // The application config is only set if the parent config is set, so
        // we don't need to call parent::getHelperSet() here
        return $actualHelperSet;
    }

    /**
     * Returns the command handler to execute when the command is run.
     *
     * This method is identical to {@link CommandConfig::getHandler()}, except
     * that the creation of the command handler is delegated to the parent
     * configuration if no callback was set and the configuration does not
     * implement {@link Runnable}.
     *
     * @param Command $command The command to handle.
     *
     * @return CommandHandler The command handler.
     */
    public function getHandler(Command $command)
    {
        $handler = parent::getHandler($command);

        if ($handler instanceof NullHandler) {
            // Delegate to the parent config
            return $this->parentConfig->getHandler($command);
        }

        return $handler;
    }
}
