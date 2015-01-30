<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Input;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Input\InputOption;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputOptionTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $option = new InputOption('option');

        $this->assertSame('option', $option->getLongName());
        $this->assertNull($option->getShortName());
        $this->assertTrue($option->isLongNamePreferred());
        $this->assertFalse($option->isShortNamePreferred());
        $this->assertFalse($option->acceptsValue());
        $this->assertFalse($option->isValueRequired());
        $this->assertFalse($option->isValueOptional());
        $this->assertFalse($option->isMultiValued());
        $this->assertNull($option->getDefaultValue());
        $this->assertSame('', $option->getDescription());
        $this->assertSame('...', $option->getValueName());
    }

    public function testDashedLongName()
    {
        $option = new InputOption('--option');

        $this->assertSame('option', $option->getLongName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfLongNameNull()
    {
        new InputOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfLongNameEmpty()
    {
        new InputOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfLongNameNoString()
    {
        new InputOption(1234);
    }

    public function testShortName()
    {
        $option = new InputOption('option', 'o');

        $this->assertSame('o', $option->getShortName());
    }

    public function testDashedShortName()
    {
        $option = new InputOption('option', '-o');

        $this->assertSame('o', $option->getShortName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfShortNameEmpty()
    {
        new InputOption('option', '');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfShortNameNoString()
    {
        new InputOption('option', 1234);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfShortNameLongerThanOneLetter()
    {
        new InputOption('option', 'ab');
    }

    public function testNoValue()
    {
        $option = new InputOption('option', null, InputOption::VALUE_NONE);

        $this->assertFalse($option->acceptsValue());
        $this->assertFalse($option->isValueRequired());
        $this->assertFalse($option->isValueOptional());
        $this->assertFalse($option->isMultiValued());
        $this->assertNull($option->getDefaultValue());
    }

    /**
     * @expectedException \Webmozart\Console\Api\Input\InvalidDefaultValueException
     */
    public function testFailIfNoValueAndDefaultValue()
    {
        new InputOption('option', null, InputOption::VALUE_NONE, '', 'Default');
    }

    public function testOptionalValue()
    {
        $option = new InputOption('option', null, InputOption::VALUE_OPTIONAL);

        $this->assertTrue($option->acceptsValue());
        $this->assertFalse($option->isValueRequired());
        $this->assertTrue($option->isValueOptional());
        $this->assertFalse($option->isMultiValued());
        $this->assertNull($option->getDefaultValue());
    }

    public function testOptionalValueWithDefaultValue()
    {
        $option = new InputOption('option', null, InputOption::VALUE_OPTIONAL, '', 'Default');

        $this->assertTrue($option->acceptsValue());
        $this->assertFalse($option->isValueRequired());
        $this->assertTrue($option->isValueOptional());
        $this->assertFalse($option->isMultiValued());
        $this->assertSame('Default', $option->getDefaultValue());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfOptionalAndNoValue()
    {
        new InputOption('option', null, InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL);
    }

    public function testRequiredValue()
    {
        $option = new InputOption('option', null, InputOption::VALUE_REQUIRED);

        $this->assertTrue($option->acceptsValue());
        $this->assertTrue($option->isValueRequired());
        $this->assertFalse($option->isValueOptional());
        $this->assertFalse($option->isMultiValued());
        $this->assertNull($option->getDefaultValue());
    }

    public function testRequiredValueWithDefaultValue()
    {
        $option = new InputOption('option', null, InputOption::VALUE_REQUIRED, '', 'Default');

        $this->assertTrue($option->acceptsValue());
        $this->assertTrue($option->isValueRequired());
        $this->assertFalse($option->isValueOptional());
        $this->assertFalse($option->isMultiValued());
        $this->assertSame('Default', $option->getDefaultValue());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfRequiredAndNoValue()
    {
        new InputOption('option', null, InputOption::VALUE_NONE | InputOption::VALUE_REQUIRED);
    }

    public function testMultiValued()
    {
        $option = new InputOption('option', null, InputOption::MULTI_VALUED);

        $this->assertTrue($option->acceptsValue());
        $this->assertTrue($option->isValueRequired());
        $this->assertFalse($option->isValueOptional());
        $this->assertTrue($option->isMultiValued());
        $this->assertSame(array(), $option->getDefaultValue());
    }

    public function testMultiValuedWithDefaultValue()
    {
        $option = new InputOption('option', null, InputOption::MULTI_VALUED, '', array('one', 'two'));

        $this->assertTrue($option->acceptsValue());
        $this->assertTrue($option->isValueRequired());
        $this->assertFalse($option->isValueOptional());
        $this->assertTrue($option->isMultiValued());
        $this->assertSame(array('one', 'two'), $option->getDefaultValue());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfMultiValuedAndNoValue()
    {
        new InputOption('option', null, InputOption::VALUE_NONE | InputOption::MULTI_VALUED);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfMultiValuedAndOptional()
    {
        new InputOption('option', null, InputOption::VALUE_OPTIONAL | InputOption::MULTI_VALUED);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Input\InvalidDefaultValueException
     */
    public function testFailIfMultiValuedAndDefaultValueNoArray()
    {
        new InputOption('option', null, InputOption::MULTI_VALUED, '', 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfFlagsNoInt()
    {
        new InputOption('option', null, '0');
    }

    public function testSetDescription()
    {
        $option = new InputOption('option', null, 0, 'Description');

        $this->assertSame('Description', $option->getDescription());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfDescriptionNoString()
    {
        new InputOption('option', null, 0, 1234);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfDescriptionNull()
    {
        new InputOption('option', null, 0, null);
    }

    public function testSetValueName()
    {
        $option = new InputOption('option', null, 0, '', null, 'value-name');

        $this->assertSame('value-name', $option->getValueName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfValueNameNoString()
    {
        new InputOption('option', null, 0, '', null, 1234);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfValueNameNull()
    {
        new InputOption('option', null, 0, '', null, null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfValueNameEmpty()
    {
        new InputOption('option', null, 0, '', null, '');
    }

    public function testPreferLongName()
    {
        $option = new InputOption('option', null, InputOption::PREFER_LONG_NAME);

        $this->assertTrue($option->isLongNamePreferred());
        $this->assertFalse($option->isShortNamePreferred());
    }

    public function testPreferShortName()
    {
        $option = new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME);

        $this->assertFalse($option->isLongNamePreferred());
        $this->assertTrue($option->isShortNamePreferred());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfNoShortNameAndPreferShortName()
    {
        new InputOption('option', null, InputOption::PREFER_SHORT_NAME);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfPreferShortNameAndPreferLongName()
    {
        new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME | InputOption::PREFER_LONG_NAME);
    }

    /**
     * @dataProvider getEqualTests
     */
    public function testEquals(InputOption $a, InputOption $b)
    {
        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }

    /**
     * @dataProvider getNotEqualTests
     */
    public function testNotEquals(InputOption $a, InputOption $b)
    {
        $this->assertFalse($a->equals($b));
        $this->assertFalse($b->equals($a));
    }

    public function getEqualTests()
    {
        return array(
            array(new InputOption('option'), new InputOption('option')),
            array(new InputOption('option'), new InputOption('option', null, 0, 'Description')),
            array(new InputOption('option'), new InputOption('option', null, 0, '', null, 'value-name')),
            array(new InputOption('option', 'o'), new InputOption('option', 'o')),
            array(new InputOption('option', 'o', InputOption::VALUE_NONE), new InputOption('option', 'o', InputOption::VALUE_NONE)),
            array(new InputOption('option', 'o', InputOption::VALUE_REQUIRED), new InputOption('option', 'o', InputOption::VALUE_REQUIRED)),
            array(new InputOption('option', 'o', InputOption::VALUE_OPTIONAL), new InputOption('option', 'o', InputOption::VALUE_OPTIONAL)),
            array(new InputOption('option', 'o', InputOption::MULTI_VALUED), new InputOption('option', 'o', InputOption::MULTI_VALUED)),
            array(new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME)),
            array(new InputOption('option', 'o', InputOption::PREFER_LONG_NAME), new InputOption('option', 'o', InputOption::PREFER_LONG_NAME)),
            array(new InputOption('option', 'o', InputOption::VALUE_NONE | InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::VALUE_NONE | InputOption::PREFER_SHORT_NAME)),
            array(new InputOption('option', 'o', InputOption::VALUE_REQUIRED | InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::VALUE_REQUIRED | InputOption::PREFER_SHORT_NAME)),
            array(new InputOption('option', 'o', InputOption::VALUE_OPTIONAL | InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::VALUE_OPTIONAL | InputOption::PREFER_SHORT_NAME)),
            array(new InputOption('option', 'o', InputOption::MULTI_VALUED | InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::MULTI_VALUED | InputOption::PREFER_SHORT_NAME)),
            array(new InputOption('option', 'o', InputOption::VALUE_OPTIONAL, '', 'foo'), new InputOption('option', 'o', InputOption::VALUE_OPTIONAL, '', 'foo')),
            array(new InputOption('option', 'o', InputOption::MULTI_VALUED, '', null), new InputOption('option', 'o', InputOption::MULTI_VALUED, '', array())),
        );
    }

    public function getNotEqualTests()
    {
        return array(
            array(new InputOption('option'), new InputOption('optio')),
            array(new InputOption('option'), new InputOption('option', 'o')),
            array(new InputOption('option', 'o', InputOption::VALUE_NONE), new InputOption('option', 'o', InputOption::VALUE_REQUIRED)),
            array(new InputOption('option', 'o', InputOption::VALUE_NONE), new InputOption('option', 'o', InputOption::VALUE_OPTIONAL)),
            array(new InputOption('option', 'o', InputOption::VALUE_NONE), new InputOption('option', 'o', InputOption::MULTI_VALUED)),
            array(new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::PREFER_LONG_NAME)),
            array(new InputOption('option', 'o', InputOption::VALUE_NONE | InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::VALUE_NONE | InputOption::PREFER_LONG_NAME)),
            array(new InputOption('option', 'o', InputOption::VALUE_REQUIRED | InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::VALUE_REQUIRED | InputOption::PREFER_LONG_NAME)),
            array(new InputOption('option', 'o', InputOption::VALUE_OPTIONAL | InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::VALUE_OPTIONAL | InputOption::PREFER_LONG_NAME)),
            array(new InputOption('option', 'o', InputOption::MULTI_VALUED | InputOption::PREFER_SHORT_NAME), new InputOption('option', 'o', InputOption::MULTI_VALUED | InputOption::PREFER_LONG_NAME)),
            array(new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME | InputOption::VALUE_NONE), new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME | InputOption::VALUE_REQUIRED)),
            array(new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME | InputOption::VALUE_NONE), new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME | InputOption::VALUE_OPTIONAL)),
            array(new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME | InputOption::VALUE_NONE), new InputOption('option', 'o', InputOption::PREFER_SHORT_NAME | InputOption::MULTI_VALUED)),
            array(new InputOption('option', 'o', InputOption::VALUE_OPTIONAL, '', null), new InputOption('option', 'o', InputOption::VALUE_OPTIONAL, '', 'null')),
            array(new InputOption('option', 'o', InputOption::VALUE_OPTIONAL, '', 1), new InputOption('option', 'o', InputOption::VALUE_OPTIONAL, '', '1')),
            array(new InputOption('option', 'o', InputOption::VALUE_OPTIONAL, '', 'foo'), new InputOption('option', 'o', InputOption::VALUE_OPTIONAL, '', 'bar')),
            array(new InputOption('option', 'o', InputOption::MULTI_VALUED, '', array('foo')), new InputOption('option', 'o', InputOption::MULTI_VALUED, '', array('bar'))),
        );
    }
}
