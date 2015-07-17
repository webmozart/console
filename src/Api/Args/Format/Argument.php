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
 * An input argument.
 *
 * Args arguments are passed after the command name and its options. In the
 * example below, "localhost" is the argument to the "server -d" command.
 *
 * ```
 * $ console server -d localhost
 * ```
 *
 * Arguments can be either optional or required. By default, all arguments are
 * optional, but you can explicitly make an argument optional or required by
 * passing one of the flags {@link OPTIONAL} and {@link REQUIRED} to the
 * constructor:
 *
 * ```php
 * $argument = new Argument('server', Argument::REQUIRED);
 * ```
 *
 * Arguments can also be multi-valued. Multi-valued arguments can be passed any
 * number of times:
 *
 * ```
 * $ console server -d localhost google.com
 * ```
 *
 * To create a multi-valued argument, pass the flag {@link MULTI_VALUED} to the
 * constructor:
 *
 * ```php
 * $argument = new Argument('server', Argument::MULTI_VALUED);
 * ```
 *
 * You can combine the {@link MULTI_VALUED} flag with either {@link OPTIONAL}
 * or {@link REQUIRED} using the bitwise operator "|":
 *
 * ```php
 * $argument = new Argument('server', Argument::REQUIRED | Argument::MULTI_VALUED);
 * ```
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Argument
{
    /**
     * Flag: The argument is required.
     */
    const REQUIRED = 1;

    /**
     * Flag: The argument is optional.
     */
    const OPTIONAL = 2;

    /**
     * Flag: The argument can be repeated multiple times.
     */
    const MULTI_VALUED = 4;

    /**
     * Flag: The value is parsed as string.
     */
    const STRING = 16;

    /**
     * Flag: The value is parsed as boolean.
     */
    const BOOLEAN = 32;

    /**
     * Flag: The value is parsed as integer.
     */
    const INTEGER = 64;

    /**
     * Flag: The value is parsed as float.
     */
    const FLOAT = 128;

    /**
     * Flag: The value "null" should be parsed as `null`.
     */
    const NULLABLE = 256;

    /**
     * @var string
     */
    private $name;

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
     * Creates a new argument.
     *
     * @param string $name         The argument name
     * @param int    $flags        A bitwise combination of the flag constants.
     * @param string $description  A human-readable description of the argument.
     * @param mixed  $defaultValue The default value of the argument (must be
     *                             null for the flag {@link self::REQUIRED}).
     */
    public function __construct($name, $flags = 0, $description = null, $defaultValue = null)
    {
        Assert::string($name, 'The argument name must be a string. Got: %s');
        Assert::notEmpty($name, 'The argument name must not be empty.');
        Assert::startsWithLetter($name, 'The argument name must start with a letter.');
        Assert::regex($name, '~^[a-zA-Z0-9\-]+$~', 'The argument name must contain letters, digits and hyphens only.');
        Assert::nullOrString($description, 'The argument description must be a string or null. Got: %s');
        Assert::nullOrNotEmpty($description, 'The argument description must not be empty.');

        $this->assertFlagsValid($flags);

        $this->addDefaultFlags($flags);

        $this->name = $name;
        $this->flags = $flags;
        $this->description = $description;
        $this->defaultValue = $this->isMultiValued() ? array() : null;

        if ($this->isOptional() || null !== $defaultValue) {
            $this->setDefaultValue($defaultValue);
        }
    }

    /**
     * Returns the name of the argument.
     *
     * @return string The argument name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns whether the argument is required.
     *
     * @return bool Returns `true` if the flag {@link REQUIRED} was passed to
     *              the constructor.
     */
    public function isRequired()
    {
        return (bool) (self::REQUIRED & $this->flags);
    }

    /**
     * Returns whether the argument is optional.
     *
     * @return bool Returns `true` if the flag {@link OPTIONAL} was passed to
     *              the constructor.
     */
    public function isOptional()
    {
        return (bool) (self::OPTIONAL & $this->flags);
    }

    /**
     * Returns whether the argument accepts multiple values.
     *
     * @return bool Returns `true` if the flag {@link MULTI_VALUED} was
     *              passed to the constructor.
     */
    public function isMultiValued()
    {
        return (bool) (self::MULTI_VALUED & $this->flags);
    }

    /**
     * Sets the default value.
     *
     * If the argument is required, this method throws an exception.
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
        if ($this->isRequired()) {
            throw new InvalidValueException('Required arguments do not accept default values.');
        }

        if ($this->isMultiValued()) {
            if (null === $defaultValue) {
                $defaultValue = array();
            } elseif (!is_array($defaultValue)) {
                throw new InvalidValueException(sprintf(
                    'The default value of a multi-valued argument must be an '.
                    'array. Got: %s',
                    is_object($defaultValue) ? get_class($defaultValue) : gettype($defaultValue)
                ));
            }
        }

        $this->defaultValue = $defaultValue;
    }

    /**
     * Returns the default value of the argument.
     *
     * @return mixed The default value.
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Parses an argument value.
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
     * Returns the description text.
     *
     * @return string The description text.
     */
    public function getDescription()
    {
        return $this->description;
    }

    private function assertFlagsValid($flags)
    {
        Assert::integer($flags, 'The argument flags must be an integer. Got: %s');

        if (($flags & self::REQUIRED) && ($flags & self::OPTIONAL)) {
            throw new InvalidArgumentException('The argument flags REQUIRED and OPTIONAL cannot be combined.');
        }

        if ($flags & self::STRING) {
            if ($flags & self::BOOLEAN) {
                throw new InvalidArgumentException('The argument flags STRING and BOOLEAN cannot be combined.');
            }

            if ($flags & self::INTEGER) {
                throw new InvalidArgumentException('The argument flags STRING and INTEGER cannot be combined.');
            }

            if ($flags & self::FLOAT) {
                throw new InvalidArgumentException('The argument flags STRING and FLOAT cannot be combined.');
            }
        } elseif ($flags & self::BOOLEAN) {
            if ($flags & self::INTEGER) {
                throw new InvalidArgumentException('The argument flags BOOLEAN and INTEGER cannot be combined.');
            }

            if ($flags & self::FLOAT) {
                throw new InvalidArgumentException('The argument flags BOOLEAN and FLOAT cannot be combined.');
            }
        } elseif ($flags & self::INTEGER) {
            if ($flags & self::FLOAT) {
                throw new InvalidArgumentException('The argument flags INTEGER and FLOAT cannot be combined.');
            }
        }
    }

    private function addDefaultFlags(&$flags)
    {
        if (!($flags & (self::REQUIRED | self::OPTIONAL))) {
            $flags |= self::OPTIONAL;
        }

        if (!($flags & (self::STRING | self::BOOLEAN | self::INTEGER | self::FLOAT))) {
            $flags |= self::STRING;
        }
    }
}
