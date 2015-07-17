<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Args\Format;

use InvalidArgumentException;
use Webmozart\Assert\Assert;
use Webmozart\Console\Util\StringUtil;

/**
 * An input option.
 *
 * Args options are passed after the command name(s). Each option has a
 * long name that is prefixed by two dashes ("--") and optionally a short name
 * that is prefixed by one dash only ("-"). The long name must have at least
 * two characters, the short name must contain a single letter only.
 *
 * In the example below, "--verbose" and "-v" are the long and short names of
 * the same option:
 *
 * ```
 * $ console server --verbose
 * $ console server -v
 * ```
 *
 * The long and short names are passed to the constructor of this class. The
 * leading dashes can be omitted:
 *
 * ```php
 * $option = new Option('verbose', 'v');
 * ```
 *
 * If an option accepts a value, you must pass one of the flags
 * {@link VALUE_REQUIRED}, {@link VALUE_OPTIONAL} or {@link MULTI_VALUED} to
 * the constructor:
 *
 * ```php
 * $option = new Option('format', 'f', Option::VALUE_REQUIRED);
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
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Option extends AbstractOption
{
    /**
     * Flag: The option has no value.
     */
    const NO_VALUE = 4;

    /**
     * Flag: The option has a required value.
     */
    const REQUIRED_VALUE = 8;

    /**
     * Flag: The option has an optional value.
     */
    const OPTIONAL_VALUE = 16;

    /**
     * Flag: The option can be stated multiple times with different values.
     */
    const MULTI_VALUED = 32;

    /**
     * Flag: The option value is parsed as string.
     */
    const STRING = 128;

    /**
     * Flag: The option value is parsed as boolean.
     */
    const BOOLEAN = 256;

    /**
     * Flag: The option value is parsed as integer.
     */
    const INTEGER = 512;

    /**
     * Flag: The option value is parsed as float.
     */
    const FLOAT = 1024;

    /**
     * Flag: The option value "null" should be parsed as `null`.
     */
    const NULLABLE = 2048;

    /**
     * @var mixed
     */
    private $defaultValue;

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
     * @throws InvalidValueException If the default value is invalid.
     */
    public function __construct($longName, $shortName = null, $flags = 0, $description = null, $defaultValue = null, $valueName = '...')
    {
        Assert::string($valueName, 'The option value name must be a string. Got: %s');
        Assert::notEmpty($valueName, 'The option value name must not be empty.');

        $this->assertFlagsValid($flags);
        $this->addDefaultFlags($flags);

        parent::__construct($longName, $shortName, $flags, $description);

        $this->valueName = $valueName;

        if ($this->acceptsValue() || null !== $defaultValue) {
            $this->setDefaultValue($defaultValue);
        }
    }

    /**
     * Returns whether the option accepts a value.
     *
     * @return bool Returns `true` if a value flag other than {@link VALUE_NONE}
     *              was passed to the constructor.
     */
    public function acceptsValue()
    {
        return !(self::NO_VALUE & $this->flags);
    }

    /**
     * Parses an option value.
     *
     * Pass one of the flags {@link STRING}, {@link BOOLEAN}, {@link INTEGER}
     * and {@link FLOAT} to the constructor to configure the result of this
     * method. You can optionally combine the flags with {@link NULLABLE} to
     * support the conversion of "null" to `null`.
     *
     * @param mixed $value The value to parse.
     *
     * @return mixed The parsed value.
     *
     * @throws InvalidValueException
     */
    public function parseValue($value)
    {
        $nullable = ($this->flags & self::NULLABLE);

        if ($this->flags & self::BOOLEAN) {
            return StringUtil::parseBoolean($value, $nullable);
        }

        if ($this->flags & self::INTEGER) {
            return StringUtil::parseInteger($value, $nullable);
        }

        if ($this->flags & self::FLOAT) {
            return StringUtil::parseFloat($value, $nullable);
        }

        return StringUtil::parseString($value, $nullable);
    }

    /**
     * Returns whether the option requires a value.
     *
     * @return bool Returns `true` if the flag {@link VALUE_REQUIRED} was
     *              passed to the constructor.
     */
    public function isValueRequired()
    {
        return (bool) (self::REQUIRED_VALUE & $this->flags);
    }

    /**
     * Returns whether the option takes an optional value.
     *
     * @return bool Returns `true` if the flag {@link VALUE_OPTIONAL} was
     *              passed to the constructor.
     */
    public function isValueOptional()
    {
        return (bool) (self::OPTIONAL_VALUE & $this->flags);
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
     * @throws InvalidValueException If the default value is invalid.
     */
    public function setDefaultValue($defaultValue = null)
    {
        if (!$this->acceptsValue()) {
            throw new InvalidValueException('Cannot set a default value when using the flag VALUE_NONE.');
        }

        if ($this->isMultiValued()) {
            if (null === $defaultValue) {
                $defaultValue = array();
            } elseif (!is_array($defaultValue)) {
                throw new InvalidValueException(sprintf(
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

    private function assertFlagsValid($flags)
    {
        Assert::integer($flags, 'The option flags must be an integer. Got: %s');

        if ($flags & self::NO_VALUE) {
            if ($flags & self::REQUIRED_VALUE) {
                throw new InvalidArgumentException('The option flags VALUE_NONE and VALUE_REQUIRED cannot be combined.');
            }

            if ($flags & self::OPTIONAL_VALUE) {
                throw new InvalidArgumentException('The option flags VALUE_NONE and VALUE_OPTIONAL cannot be combined.');
            }

            if ($flags & self::MULTI_VALUED) {
                throw new InvalidArgumentException('The option flags VALUE_NONE and MULTI_VALUED cannot be combined.');
            }
        }

        if (($flags & self::OPTIONAL_VALUE) && ($flags & self::MULTI_VALUED)) {
            throw new InvalidArgumentException('The option flags VALUE_OPTIONAL and MULTI_VALUED cannot be combined.');
        }

        if ($flags & self::STRING) {
            if ($flags & self::BOOLEAN) {
                throw new InvalidArgumentException('The option flags STRING and BOOLEAN cannot be combined.');
            }

            if ($flags & self::INTEGER) {
                throw new InvalidArgumentException('The option flags STRING and INTEGER cannot be combined.');
            }

            if ($flags & self::FLOAT) {
                throw new InvalidArgumentException('The option flags STRING and FLOAT cannot be combined.');
            }
        } elseif ($flags & self::BOOLEAN) {
            if ($flags & self::INTEGER) {
                throw new InvalidArgumentException('The option flags BOOLEAN and INTEGER cannot be combined.');
            }

            if ($flags & self::FLOAT) {
                throw new InvalidArgumentException('The option flags BOOLEAN and FLOAT cannot be combined.');
            }
        } elseif ($flags & self::INTEGER) {
            if ($flags & self::FLOAT) {
                throw new InvalidArgumentException('The option flags INTEGER and FLOAT cannot be combined.');
            }
        }
    }

    private function addDefaultFlags(&$flags)
    {
        if (!($flags & (self::NO_VALUE | self::REQUIRED_VALUE | self::OPTIONAL_VALUE | self::MULTI_VALUED))) {
            $flags |= self::NO_VALUE;
        }

        if (!($flags & (self::STRING | self::BOOLEAN | self::INTEGER | self::FLOAT))) {
            $flags |= self::STRING;
        }

        if (($flags & self::MULTI_VALUED) && !($flags & self::REQUIRED_VALUE)) {
            $flags |= self::REQUIRED_VALUE;
        }
    }
}
