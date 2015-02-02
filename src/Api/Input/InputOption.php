<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Input;

use Symfony\Component\Process\Exception\InvalidArgumentException;
use Webmozart\Console\Assert\Assert;

/**
 * A command line option.
 *
 * Command line options are passed after the command name. Each option has a
 * long name that is prefixed by two dashes ("--") and optionally a short name
 * that is prefixed by one dash only ("-"). The long name must have at least
 * two characters, the short name contains one single letter only.
 *
 * In the example below, "--all" and "-a" are the long and short names of the
 * same option:
 *
 * ```
 * $ ls -a
 * $ ls --all
 * ```
 *
 * The long and short names are passed to the constructor of this class. The
 * leading dashes can be omitted:
 *
 * ```php
 * $option = new InputOption('all', 'a');
 * ```
 *
 * If an option accepts a value, you must pass one of the flags
 * {@link VALUE_REQUIRED}, {@link VALUE_OPTIONAL} or {@link MULTI_VALUED} to
 * the constructor:
 *
 * ```php
 * $option = new InputOption('format', null, InputOption::VALUE_REQUIRED);
 * ```
 *
 *  * The flag {@link VALUE_REQUIRED} indicates that a value must always be
 *    passed.
 *  * The flag {@link VALUE_OPTIONAL} indicates that a value may optionally be
 *    passed. If no value is passed, the default value passed to the constructor
 *    is returned, which defaults to `null`.
 *  * The flag {@link MULTI_VALUED} indicates that the option can be passed
 *    multiple times with different values. The passed values are returned to
 *    the application as array. The value of a multi-valued option is always
 *    required.
 *
 * @since  1.0
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputOption
{
    /**
     * Flag: The option has no value.
     */
    const VALUE_NONE = 1;

    /**
     * Flag: The option has a required value.
     */
    const VALUE_REQUIRED = 2;

    /**
     * Flag: The option has an optional value.
     */
    const VALUE_OPTIONAL = 4;

    /**
     * Flag: The option can be stated multiple times with different values.
     */
    const MULTI_VALUED = 8;

    /**
     * Flag: Prefer usage of the long option name.
     */
    const PREFER_LONG_NAME = 16;

    /**
     * Flag: Prefer usage of the short option name.
     */
    const PREFER_SHORT_NAME = 32;

    /**
     * @var null|string
     */
    private $longName;

    /**
     * @var null|string
     */
    private $shortName;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $valueName;

    /**
     * Creates a new option.
     *
     * @param string      $longName     The long option name.
     * @param string|null $shortName    The short option name.
     * @param int         $flags        A bitwise combination of the option flag
     *                                  constants.
     * @param string      $description  A human-readable description of the option.
     * @param mixed       $defaultValue The default value (must be null for
     *                                  {@link VALUE_REQUIRED} or
     *                                  {@link VALUE_NONE}).
     * @param string      $valueName    The name of the value to be used in
     *                                  usage examples of the option.
     *
     * @throws InvalidDefaultValueException If the default value is invalid.
     */
    public function __construct($longName, $shortName = null, $flags = 0, $description = '', $defaultValue = null, $valueName = '...')
    {
        $longName = $this->removeDoubleDashPrefix($longName);
        $shortName = $this->removeDashPrefix($shortName);

        Assert::string($description, 'The option description must be a string. Got: %s');
        Assert::string($valueName, 'The option value name must be a string. Got: %s');
        Assert::notEmpty($valueName, 'The option value name must not be empty.');

        $this->assertFlagsValid($flags);
        $this->assertLongNameValid($longName);
        $this->assertShortNameValid($shortName, $flags);

        $this->addDefaultFlags($flags);

        $this->longName = $longName;
        $this->shortName = $shortName;
        $this->flags = $flags;
        $this->description = $description;
        $this->valueName = $valueName;

        if ($this->acceptsValue() || null !== $defaultValue) {
            $this->setDefaultValue($defaultValue);
        }
    }

    /**
     * Returns the long option name.
     *
     * The long name is prefixed with a double dash ("--") on the console.
     *
     * @return string The long name.
     */
    public function getLongName()
    {
        return $this->longName;
    }

    /**
     * Returns whether using the long name is preferred over using the short name.
     *
     * @return bool Returns `true` if the long name is preferred over the short
     *              name.
     */
    public function isLongNamePreferred()
    {
        return (bool) (self::PREFER_LONG_NAME & $this->flags);
    }

    /**
     * Returns the short option name.
     *
     * The short name is prefixed with a single dash ("-") on the console. The
     * short name always consists of one character only.
     *
     * @return string The short name.
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Returns whether using the short name is preferred over using the long name.
     *
     * @return bool Returns `true` if the short name is preferred over the long
     *              name.
     */
    public function isShortNamePreferred()
    {
        return (bool) (self::PREFER_SHORT_NAME & $this->flags);
    }

    /**
     * Returns whether the option accepts a value.
     *
     * @return bool Returns `true` if a value flag other than {@link VALUE_NONE}
     *              was passed to the constructor.
     */
    public function acceptsValue()
    {
        return !(self::VALUE_NONE & $this->flags);
    }

    /**
     * Returns whether the option requires a value.
     *
     * @return bool Returns `true` if the flag {@link VALUE_REQUIRED} was
     *              passed to the constructor.
     */
    public function isValueRequired()
    {
        return (bool) (self::VALUE_REQUIRED & $this->flags);
    }

    /**
     * Returns whether the option takes an optional value.
     *
     * @return bool Returns `true` if the flag {@link VALUE_OPTIONAL} was
     *              passed to the constructor.
     */
    public function isValueOptional()
    {
        return (bool) (self::VALUE_OPTIONAL & $this->flags);
    }

    /**
     * Returns whether the option accepts multiple values.
     *
     * @return bool Returns `true` if the flag {@link MULTI_VALUED} was
     *              passed to the constructor.
     */
    public function isMultiValued()
    {
        return (bool) (self::MULTI_VALUED & $this->flags);
    }

    /**
     * Sets the default value of the option.
     *
     * If the option does not accept a value, this method throws an exception.
     *
     * If the option is multi-valued, the passed value must be an array or
     * `null`.
     *
     * @param mixed $defaultValue The default value.
     *
     * @throws InvalidDefaultValueException If the default value is invalid.
     */
    public function setDefaultValue($defaultValue = null)
    {
        if (!$this->acceptsValue()) {
            throw new InvalidDefaultValueException('Cannot set a default value when using the flag VALUE_NONE.');
        }

        if ($this->isMultiValued()) {
            if (null === $defaultValue) {
                $defaultValue = array();
            } elseif (!is_array($defaultValue)) {
                throw new InvalidDefaultValueException(sprintf(
                    'The default value of a multi-valued option must be an '.
                    'array. Got: %s',
                    is_object($defaultValue) ? get_class($defaultValue) : gettype($defaultValue)
                ));
            }
        }

        $this->defaultValue = $defaultValue;
    }

    /**
     * Returns the default value of the option.
     *
     * @return mixed The default value.
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Returns the description text.
     *
     * @return string The description text.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the name of the option value.
     *
     * This name can be used as placeholder of the value when displaying the
     * option's usage.
     *
     * @return string The name of the option value.
     */
    public function getValueName()
    {
        return $this->valueName;
    }

    /**
     * Returns whether the option equals another option.
     *
     * The description and the value name are ignored when comparing the
     * options.
     *
     * @param InputOption $other The option to compare.
     *
     * @return bool Returns `true` if the options are equal.
     */
    public function equals(InputOption $other)
    {
        return $other->longName === $this->longName
            && $other->shortName === $this->shortName
            && $other->flags === $this->flags
            && $other->defaultValue === $this->defaultValue;
    }

    private function assertFlagsValid($flags)
    {
        Assert::integer($flags, 'The option flags must be an integer. Got: %s');

        if ($flags & self::VALUE_NONE) {
            if ($flags & self::VALUE_REQUIRED) {
                throw new InvalidArgumentException('The option flags VALUE_NONE and VALUE_REQUIRED cannot be combined.');
            }

            if ($flags & self::VALUE_OPTIONAL) {
                throw new InvalidArgumentException('The option flags VALUE_NONE and VALUE_OPTIONAL cannot be combined.');
            }

            if ($flags & self::MULTI_VALUED) {
                throw new InvalidArgumentException('The option flags VALUE_NONE and MULTI_VALUED cannot be combined.');
            }
        }

        if (($flags & self::VALUE_OPTIONAL) && ($flags & self::MULTI_VALUED)) {
            throw new InvalidArgumentException('The option flags VALUE_OPTIONAL and MULTI_VALUED cannot be combined.');
        }

        if (($flags & self::PREFER_SHORT_NAME) && ($flags & self::PREFER_LONG_NAME)) {
            throw new InvalidArgumentException('The option flags PREFER_SHORT_NAME and PREFER_LONG_NAME cannot be combined.');
        }
    }

    private function assertLongNameValid($longName)
    {
        Assert::string($longName, 'The long option name must be a string. Got: %s');
        Assert::notEmpty($longName, 'The long option name must not be empty.');
        Assert::greaterThan(strlen($longName), 1, sprintf('The long option name must contain more than one character. Got: "%s"', $longName));
        Assert::startsWithLetter($longName, 'The long option name must start with a letter.');
        Assert::regex($longName, '~^[a-zA-Z0-9\-]+$~', 'The long option name must contain letters, digits and hyphens only.');
    }

    private function assertShortNameValid($shortName, $flags)
    {
        Assert::nullOrString($shortName, 'The short option name must be a string or null. Got: %s');
        Assert::nullOrNotEmpty($shortName, 'The short option name must not be empty.');

        if (null !== $shortName) {
            Assert::true(1 === strlen($shortName), sprintf('The short option name must be exactly one letter. Got: "%s"', $shortName));
        }

        if (null === $shortName && ($flags & self::PREFER_SHORT_NAME)) {
            throw new InvalidArgumentException('The short option name must be given if the option flag PREFER_SHORT_NAME is selected.');
        }
    }

    private function removeDoubleDashPrefix($string)
    {
        if (0 === strpos($string, '--')) {
            $string = substr($string, 2);
        }

        return $string;
    }

    private function removeDashPrefix($string)
    {
        if (0 === strpos($string, '-')) {
            $string = substr($string, 1);
        }

        return $string;
    }

    private function addDefaultFlags(&$flags)
    {
        if (!($flags & (self::VALUE_NONE | self::VALUE_REQUIRED | self::VALUE_OPTIONAL | self::MULTI_VALUED))) {
            $flags |= self::VALUE_NONE;
        }

        if (($flags & self::MULTI_VALUED) && !($flags & self::VALUE_REQUIRED)) {
            $flags |= self::VALUE_REQUIRED;
        }

        if (!($flags & (self::PREFER_LONG_NAME | self::PREFER_SHORT_NAME))) {
            $flags |= self::PREFER_LONG_NAME;
        }
    }
}
