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
 * Base class for command line options.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractOption
{
    /**
     * Flag: Prefer usage of the long option name.
     */
    const PREFER_LONG_NAME = 1;

    /**
     * Flag: Prefer usage of the short option name.
     */
    const PREFER_SHORT_NAME = 2;

    /**
     * @var int
     */
    protected $flags;

    /**
     * @var null|string
     */
    private $longName;

    /**
     * @var null|string
     */
    private $shortName;

    /**
     * @var string
     */
    private $description;

    /**
     * Creates a new option.
     *
     * @param string      $longName     The long option name.
     * @param string|null $shortName    The short option name.
     * @param int         $flags        A bitwise combination of the option flag
     *                                  constants.
     * @param string      $description  A human-readable description of the option.
     *
     * @throws InvalidDefaultValueException If the default value is invalid.
     */
    public function __construct($longName, $shortName = null, $flags = 0, $description = null)
    {
        $longName = $this->removeDoubleDashPrefix($longName);
        $shortName = $this->removeDashPrefix($shortName);

        Assert::nullOrString($description, 'The option description must be a string or null. Got: %s');
        Assert::nullOrNotEmpty($description, 'The option description must not be empty.');

        $this->assertFlagsValid($flags);
        $this->assertLongNameValid($longName);
        $this->assertShortNameValid($shortName, $flags);

        $this->addDefaultFlags($flags);

        $this->longName = $longName;
        $this->shortName = $shortName;
        $this->flags = $flags;
        $this->description = $description;
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
        Assert::integer($flags, 'The option flags must be an integer. Got: %s');

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
        if (!($flags & (self::PREFER_LONG_NAME | self::PREFER_SHORT_NAME))) {
            $flags |= self::PREFER_LONG_NAME;
        }
    }
}
