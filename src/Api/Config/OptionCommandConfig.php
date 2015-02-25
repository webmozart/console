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

use LogicException;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\CommandOption;
use Webmozart\Console\Assert\Assert;

/**
 * The configuration of an option command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class OptionCommandConfig extends SubCommandConfig
{
    /**
     * @var string
     */
    private $shortName;

    /**
     * @var bool
     */
    private $longNamePreferred;

    /**
     * Creates a new configuration.
     *
     * @param string            $name              The long option name of the command.
     * @param string            $shortName         The short option name of the command.
     * @param CommandConfig     $parentConfig      The parent configuration.
     * @param ApplicationConfig $applicationConfig The application configuration.
     */
    public function __construct($name = null, $shortName = null, CommandConfig $parentConfig = null, ApplicationConfig $applicationConfig = null)
    {
        parent::__construct($name, $parentConfig, $applicationConfig);

        $this->setShortName($shortName);
    }

    /**
     * Sets the name of the command.
     *
     * Contrary to the base implementation, the name of an option command must
     * contain at least two characters.
     *
     * @param string $name The name of the command.
     *
     * @return static The current instance.
     */
    public function setName($name)
    {
        if (null !== $name) {
            Assert::string($name, 'The command name must be a string or null. Got: %s');
            Assert::notEmpty($name, 'The command name must not be empty.');
            Assert::greaterThan(strlen($name), 1, sprintf('The command name should contain at least two characters. Got: "%s"', $name));
        }

        parent::setName($name);

        return $this;
    }

    /**
     * Alias of {@link getName()}.
     *
     * @return string The command name.
     */
    public function getLongName()
    {
        return $this->getName();
    }

    /**
     * Alias of {@link setName()}.
     *
     * @param string $name The command name.
     *
     * @return static The current instance.
     */
    public function setLongName($name)
    {
        return $this->setName($name);
    }

    /**
     * Returns the short option name of the command.
     *
     * @return string The short option name.
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Sets the short option name of the command.
     *
     * The short name must consist of a single letter. The short name is
     * preceded by a single dash "-" when calling the command:
     *
     * ```
     * $ server -d localhost
     * ```
     *
     * In the example above, "d" is the short name of the "server --delete"
     * command.
     *
     * @param string $shortName The short option name.
     *
     * @return static The current instance.
     */
    public function setShortName($shortName)
    {
        if (null !== $shortName) {
            Assert::string($shortName, 'The short command name must be a string or null. Got: %s');
            Assert::notEmpty($shortName, 'The short command name must not be empty.');
            Assert::regex($shortName, '~^[a-zA-Z]$~', 'The short command name must contain a single letter. Got: %s');
        }

        // Reset short name preference when unsetting the short name
        if (null === $shortName && false === $this->longNamePreferred) {
            $this->longNamePreferred = null;
        }

        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Marks the long name to be preferred over the short name.
     *
     * This information is mainly used in the help where the preferred name is
     * listed before alternative names.
     *
     * @return static The current instance.
     *
     * @see isLongNamePreferred(), setPreferShortName()
     */
    public function setPreferLongName()
    {
        $this->longNamePreferred = true;

        return $this;
    }

    /**
     * Marks the short name to be preferred over the long name.
     *
     * This information is mainly used in the help where the preferred name is
     * listed before alternative names.
     *
     * @return static The current instance.
     *
     * @see isShortNamePreferred(), setPreferLongName()
     */
    public function setPreferShortName()
    {
        if (null === $this->shortName) {
            throw new LogicException('No short name is set.');
        }

        $this->longNamePreferred = false;

        return $this;
    }

    /**
     * Returns whether the long name should be preferred over the short name.
     *
     * If no preference was set, the short name is preferred by default if one
     * is set. If no short name is set, the long name is preferred by default.
     *
     * @return bool Returns `true` if the long name should be preferred over the
     *              short name.
     *
     * @see setPreferLongName(), isShortNamePreferred()
     */
    public function isLongNamePreferred()
    {
        if (null === $this->longNamePreferred) {
            // If no preference is set, prefer the short name (if one is set)
            return null === $this->shortName;
        }

        return $this->longNamePreferred;
    }

    /**
     * Returns whether the short name should be preferred over the long name.
     *
     * If no preference was set, the short name is preferred by default if one
     * is set. If no short name is set, the long name is preferred by default.
     *
     * @return bool Returns `true` if the short name should be preferred over
     *              the long name.
     *
     * @see setPreferShortName(), isLongNamePreferred()
     */
    public function isShortNamePreferred()
    {
        return !$this->isLongNamePreferred();
    }

    /**
     * {@inheritdoc}
     */
    public function buildArgsFormat(ArgsFormat $baseFormat = null)
    {
        $formatBuilder = ArgsFormat::build($baseFormat);

        if (!$this->isAnonymous()) {
            $flags = $this->isLongNamePreferred()
                ? CommandOption::PREFER_LONG_NAME
                : CommandOption::PREFER_SHORT_NAME;

            $formatBuilder->addCommandOption(new CommandOption(
                $this->getName(),
                $this->getShortName(),
                $this->getAliases(),
                $flags
            ));
        }

        $formatBuilder->addOptions($this->getOptions());
        $formatBuilder->addArguments($this->getArguments());

        return $formatBuilder->getFormat();
    }

}
