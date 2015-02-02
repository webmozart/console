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

use InvalidArgumentException;
use Webmozart\Console\Assert\Assert;

/**
 * An command line argument.
 *
 * Command line arguments are passed after the command and its options. In the
 * example below, "/" is the argument to the "ls" command.
 *
 * ```
 * $ ls /
 * ```
 *
 * Arguments can be either optional or required. By default, all arguments are
 * optional, but you can explicitly make an argument optional or required by
 * passing one of the flags {@link OPTIONAL} and {@link REQUIRED} to the
 * constructor:
 *
 * ```php
 * $argument = new InputArgument('directory', InputArgument::REQUIRED);
 * ```
 *
 * Arguments can also be multi-valued. Multi-valued arguments can be passed any
 * number of times:
 *
 * ```
 * $ ls / /home /usr/share
 * ```
 *
 * To create a multi-valued argument, pass the flag {@link MULTI_VALUED} to the
 * constructor:
 *
 * ```php
 * $argument = new InputArgument('directory', InputArgument::MULTI_VALUED);
 * ```
 *
 * You can combine the {@link MULTI_VALUED} flag with either {@link OPTIONAL}
 * or {@link REQUIRED} using the bitwise operator "|":
 *
 * ```php
 * $argument = new InputArgument('directory', InputArgument::REQUIRED | InputArgument::MULTI_VALUED);
 * ```
 *
 * @since  1.0
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputArgument
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
     * @throws InvalidDefaultValueException If the default value is invalid.
     */
    public function setDefaultValue($defaultValue = null)
    {
        if ($this->isRequired()) {
            throw new InvalidDefaultValueException('Required arguments do not accept default values.');
        }

        if ($this->isMultiValued()) {
            if (null === $defaultValue) {
                $defaultValue = array();
            } elseif (!is_array($defaultValue)) {
                throw new InvalidDefaultValueException(sprintf(
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
     * Returns the description text.
     *
     * @return string The description text.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns whether the argument equals another argument.
     *
     * The description is ignored when comparing the arguments.
     *
     * @param InputArgument $other The argument to compare.
     *
     * @return bool Returns `true` if the arguments are equal.
     */
    public function equals(InputArgument $other)
    {
        return $other->name === $this->name
            && $other->flags === $this->flags
            && $other->defaultValue === $this->defaultValue;
    }

    private function assertFlagsValid($flags)
    {
        Assert::integer($flags, 'The argument flags must be an integer. Got: %s');

        if (($flags & self::REQUIRED) && ($flags & self::OPTIONAL)) {
            throw new InvalidArgumentException('The argument flags REQUIRED and OPTIONAL cannot be combined.');
        }
    }

    private function addDefaultFlags(&$flags)
    {
        if (!($flags & (self::REQUIRED | self::OPTIONAL))) {
            $flags |= self::OPTIONAL;
        }
    }
}
