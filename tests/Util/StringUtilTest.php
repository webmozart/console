<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Util;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Util\StringUtil;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StringUtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getParseStringTests
     */
    public function testParseString($input, $output, $nullable = true)
    {
        $this->assertSame($output, StringUtil::parseString($input, $nullable));
    }

    public function getParseStringTests()
    {
        return array(
            array('', ''),
            array('string', 'string'),
            array('null', null),
            array('null', 'null', false),
            array('false', 'false'),
            array('true', 'true'),
            array('no', 'no'),
            array('yes', 'yes'),
            array('off', 'off'),
            array('on', 'on'),
            array('0', '0'),
            array('1', '1'),
            array('1.23', '1.23'),
            array(null, null),
            array(null, 'null', false),
            array(true, 'true'),
            array(false, 'false'),
            array(0, '0'),
            array(1, '1'),
            array(1.23, '1.23'),
        );
    }

    /**
     * @dataProvider getValidParseBooleanTests
     */
    public function testParseBoolean($input, $output, $nullable = true)
    {
        $this->assertSame($output, StringUtil::parseBoolean($input, $nullable));
    }

    public function getValidParseBooleanTests()
    {
        return array(
            array('', false),
            array('null', null),
            array('false', false),
            array('true', true),
            array('no', false),
            array('yes', true),
            array('off', false),
            array('on', true),
            array('0', false),
            array('1', true),
            array(null, null),
            array(true, true),
            array(false, false),
            array(0, false),
            array(1, true),
        );
    }

    /**
     * @dataProvider getInvalidParseBooleanTests
     * @expectedException \Webmozart\Console\Api\Args\Format\InvalidValueException
     */
    public function testParseBooleanFailsIfInvalid($input, $nullable = true)
    {
        StringUtil::parseBoolean($input, $nullable);
    }

    public function getInvalidParseBooleanTests()
    {
        return array(
            array('string'),
            array('null', false),
            array('1.23'),
            array(null, false),
            array(1.23),
        );
    }

    /**
     * @dataProvider getValidParseIntegerTests
     */
    public function testParseInteger($input, $output, $nullable = true)
    {
        $this->assertSame($output, StringUtil::parseInteger($input, $nullable));
    }

    public function getValidParseIntegerTests()
    {
        return array(
            array('null', null),
            array('0', 0),
            array('1', 1),
            array('1.23', 1),
            array(null, null),
            array(true, 1),
            array(false, 0),
            array(0, 0),
            array(1, 1),
            array(1.23, 1),
        );
    }

    /**
     * @dataProvider getInvalidParseIntegerTests
     * @expectedException \Webmozart\Console\Api\Args\Format\InvalidValueException
     */
    public function testParseIntegerFailsIfInvalid($input, $nullable = true)
    {
        StringUtil::parseInteger($input, $nullable);
    }

    public function getInvalidParseIntegerTests()
    {
        return array(
            array(''),
            array('string'),
            array('null', false),
            array('false'),
            array('true'),
            array('no'),
            array('yes'),
            array('off'),
            array('on'),
            array(null, false),
        );
    }

    /**
     * @dataProvider getValidParseFloatTests
     */
    public function testParseFloat($input, $output, $nullable = true)
    {
        $this->assertSame($output, StringUtil::parseFloat($input, $nullable));
    }

    public function getValidParseFloatTests()
    {
        return array(
            array('null', null),
            array('0', 0.0),
            array('1', 1.0),
            array('1.23', 1.23),
            array(null, null),
            array(true, 1.0),
            array(false, 0.0),
            array(0, 0.0),
            array(1, 1.0),
            array(1.23, 1.23),
        );
    }

    /**
     * @dataProvider getInvalidParseFloatTests
     * @expectedException \Webmozart\Console\Api\Args\Format\InvalidValueException
     */
    public function testParseFloatFailsIfInvalid($input, $nullable = true)
    {
        StringUtil::parseFloat($input, $nullable);
    }

    public function getInvalidParseFloatTests()
    {
        return array(
            array(''),
            array('string'),
            array('null', false),
            array('false'),
            array('true'),
            array('no'),
            array('yes'),
            array('off'),
            array('on'),
            array(null, false),
        );
    }
}
