<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Util;

use Webmozart\Console\Api\Args\Format\InvalidValueException;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ValueParser
{
    public static function parseString($value, $nullable = true)
    {
        if ($nullable && (null === $value || 'null' === $value)) {
            return null;
        }

        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        return (string) $value;
    }

    public static function parseBoolean($value, $nullable = true)
    {
        if ($nullable && (null === $value || 'null' === $value)) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value) || is_int($value)) {
            switch ((string) $value) {
                case '':
                case 'false':
                case '0':
                case 'no':
                case 'off':
                    return false;

                case 'true':
                case '1':
                case 'yes':
                case 'on':
                    return true;
            }
        }

        throw new InvalidValueException(sprintf(
            'The value "%s" cannot be parsed as boolean.',
            $value
        ));
    }

    public static function parseInteger($value, $nullable = true)
    {
        if ($nullable && (null === $value || 'null' === $value)) {
            return null;
        }

        if (is_numeric($value) || is_bool($value)) {
            return (int) $value;
        }

        throw new InvalidValueException(sprintf(
            'The value "%s" cannot be parsed as integer.',
            $value
        ));
    }

    public static function parseFloat($value, $nullable = true)
    {
        if ($nullable && (null === $value || 'null' === $value)) {
            return null;
        }

        if (is_numeric($value) || is_bool($value)) {
            return (float) $value;
        }

        throw new InvalidValueException(sprintf(
            'The value "%s" cannot be parsed as float.',
            $value
        ));
    }

    private function __construct()
    {
    }
}
