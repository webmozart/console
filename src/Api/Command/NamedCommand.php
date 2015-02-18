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
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\ArgsFormatBuilder;
use Webmozart\Console\Api\Args\Format\CommandName;
use Webmozart\Console\Api\Args\Format\CommandOption;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\OptionCommandConfig;

/**
 * A named console command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see    Command
 */
class NamedCommand extends Command
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $shortName;

    /**
     * @var string[]
     */
    private $aliases = array();

    /**
     * Creates a new command.
     *
     * @param CommandConfig $config        The command configuration.
     * @param Application   $application   The console application.
     * @param Command       $parentCommand The parent command.
     *
     * @throws LogicException If the name of the command configuration is not set.
     */
    public function __construct(CommandConfig $config, Application $application = null, Command $parentCommand = null)
    {
        if (!$config->getName()) {
            throw new LogicException('The name of the command config must be set.');
        }

        parent::__construct($config, $application, $parentCommand);

        $this->name = $config->getName();
        $this->shortName = $config instanceof OptionCommandConfig ? $config->getShortName() : null;
        $this->aliases = $config->getAliases();
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
     * Returns the short name of the command.
     *
     * This method only returns a value if an {@link OptionCommandConfig} was
     * passed to the constructor. Otherwise this method returns `null`.
     *
     * @return string|null The short name or `null` if the command is not an
     *                     option command.
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Returns the alias names of the command.
     *
     * @return string[] An array of alias names of the command.
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Returns whether the command has aliases.
     *
     * @return bool Returns `true` if the command has aliases and `false`
     *              otherwise.
     */
    public function hasAliases()
    {
        return count($this->aliases) > 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildFormat()
    {
        $config = $this->getConfig();
        $formatBuilder = new ArgsFormatBuilder($this->getBaseFormat());

        // Add the name for named commands
        if ($config instanceof OptionCommandConfig) {
            $flags = $config->isLongNamePreferred()
                ? CommandOption::PREFER_LONG_NAME
                : CommandOption::PREFER_SHORT_NAME;

            $formatBuilder->addCommandOption(new CommandOption(
                $config->getName(),
                $config->getShortName(),
                $config->getAliases(),
                $flags
            ));
        } else {
            $formatBuilder->addCommandName(new CommandName(
                $config->getName(),
                $config->getAliases()
            ));
        }

        $formatBuilder->addOptions($config->getOptions());
        $formatBuilder->addArguments($config->getArguments());

        return $formatBuilder->getFormat();
    }
}
