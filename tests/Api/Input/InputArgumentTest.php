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
use Webmozart\Console\Api\Input\InputArgument;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputArgumentTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $argument = new InputArgument('argument');

        $this->assertSame('argument', $argument->getName());
        $this->assertFalse($argument->isRequired());
        $this->assertTrue($argument->isOptional());
        $this->assertFalse($argument->isMultiValued());
        $this->assertNull($argument->getDefaultValue());
        $this->assertSame('', $argument->getDescription());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfNameNull()
    {
        new InputArgument(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfNameEmpty()
    {
        new InputArgument('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfNameNoString()
    {
        new InputArgument(1234);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfNameContainsSpaces()
    {
        new InputArgument('foo bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfNameStartsWithHyphen()
    {
        new InputArgument('-argument');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfNameDoesNotStartWithLetter()
    {
        new InputArgument('1argument');
    }

    public function testRequiredArgument()
    {
        $argument = new InputArgument('argument', InputArgument::REQUIRED);

        $this->assertSame('argument', $argument->getName());
        $this->assertTrue($argument->isRequired());
        $this->assertFalse($argument->isOptional());
        $this->assertFalse($argument->isMultiValued());
        $this->assertNull($argument->getDefaultValue());
        $this->assertSame('', $argument->getDescription());
    }

    /**
     * @expectedException \Webmozart\Console\Api\Input\InvalidDefaultValueException
     */
    public function testFailIfRequiredArgumentAndDefaultValue()
    {
        new InputArgument('argument', InputArgument::REQUIRED, '', 'Default');
    }

    public function testOptionalArgument()
    {
        $argument = new InputArgument('argument', InputArgument::OPTIONAL);

        $this->assertSame('argument', $argument->getName());
        $this->assertFalse($argument->isRequired());
        $this->assertTrue($argument->isOptional());
        $this->assertFalse($argument->isMultiValued());
        $this->assertNull($argument->getDefaultValue());
        $this->assertSame('', $argument->getDescription());
    }

    public function testOptionalArgumentWithDefaultValue()
    {
        $argument = new InputArgument('argument', InputArgument::OPTIONAL, '', 'Default');

        $this->assertSame('argument', $argument->getName());
        $this->assertFalse($argument->isRequired());
        $this->assertTrue($argument->isOptional());
        $this->assertFalse($argument->isMultiValued());
        $this->assertSame('Default', $argument->getDefaultValue());
        $this->assertSame('', $argument->getDescription());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfArgumentRequiredAndOptional()
    {
        new InputArgument('argument', InputArgument::REQUIRED | InputArgument::OPTIONAL);
    }

    public function testMultiValuedArgument()
    {
        $argument = new InputArgument('argument', InputArgument::MULTI_VALUED);

        $this->assertSame('argument', $argument->getName());
        $this->assertFalse($argument->isRequired());
        $this->assertTrue($argument->isOptional());
        $this->assertTrue($argument->isMultiValued());
        $this->assertSame(array(), $argument->getDefaultValue());
        $this->assertSame('', $argument->getDescription());
    }

    public function testRequiredMultiValuedArgument()
    {
        $argument = new InputArgument('argument', InputArgument::MULTI_VALUED | InputArgument::REQUIRED);

        $this->assertSame('argument', $argument->getName());
        $this->assertTrue($argument->isRequired());
        $this->assertFalse($argument->isOptional());
        $this->assertTrue($argument->isMultiValued());
        $this->assertSame(array(), $argument->getDefaultValue());
        $this->assertSame('', $argument->getDescription());
    }

    public function testOptionalMultiValuedArgument()
    {
        $argument = new InputArgument('argument', InputArgument::MULTI_VALUED | InputArgument::OPTIONAL);

        $this->assertSame('argument', $argument->getName());
        $this->assertFalse($argument->isRequired());
        $this->assertTrue($argument->isOptional());
        $this->assertTrue($argument->isMultiValued());
        $this->assertSame(array(), $argument->getDefaultValue());
        $this->assertSame('', $argument->getDescription());
    }

    public function testMultiValuedArgumentWithDefaultValue()
    {
        $argument = new InputArgument('argument', InputArgument::MULTI_VALUED, '', array('one', 'two'));

        $this->assertSame('argument', $argument->getName());
        $this->assertFalse($argument->isRequired());
        $this->assertTrue($argument->isOptional());
        $this->assertTrue($argument->isMultiValued());
        $this->assertSame(array('one', 'two'), $argument->getDefaultValue());
        $this->assertSame('', $argument->getDescription());
    }

    /**
     * @expectedException \Webmozart\Console\Api\Input\InvalidDefaultValueException
     */
    public function testFailIfMultiValuedAndDefaultValueNoArray()
    {
        new InputArgument('argument', InputArgument::MULTI_VALUED, '', 'foobar');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfFlagsNoInt()
    {
        new InputArgument('argument', '0');
    }

    public function testSetDescription()
    {
        $argument = new InputArgument('argument', 0, 'Description');

        $this->assertSame('Description', $argument->getDescription());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfDescriptionNull()
    {
        new InputArgument('argument', 0, null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfDescriptionNoString()
    {
        new InputArgument('argument', 0, 1234);
    }

    /**
     * @dataProvider getEqualTests
     */
    public function testEquals(InputArgument $a, InputArgument $b)
    {
        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }

    /**
     * @dataProvider getNotEqualTests
     */
    public function testNotEquals(InputArgument $a, InputArgument $b)
    {
        $this->assertFalse($a->equals($b));
        $this->assertFalse($b->equals($a));
    }

    public function getEqualTests()
    {
        return array(
            array(new InputArgument('argument'), new InputArgument('argument')),
            array(new InputArgument('argument'), new InputArgument('argument', 0, 'Description')),
            array(new InputArgument('argument'), new InputArgument('argument')),
            array(new InputArgument('argument', InputArgument::REQUIRED), new InputArgument('argument', InputArgument::REQUIRED)),
            array(new InputArgument('argument', InputArgument::OPTIONAL), new InputArgument('argument', InputArgument::OPTIONAL)),
            array(new InputArgument('argument', InputArgument::MULTI_VALUED), new InputArgument('argument', InputArgument::MULTI_VALUED)),
            array(new InputArgument('argument', InputArgument::OPTIONAL, '', 'foo'), new InputArgument('argument', InputArgument::OPTIONAL, '', 'foo')),
            array(new InputArgument('argument', InputArgument::MULTI_VALUED, '', null), new InputArgument('argument', InputArgument::MULTI_VALUED, '', array())),
        );
    }

    public function getNotEqualTests()
    {
        return array(
            array(new InputArgument('argument'), new InputArgument('argumen')),
            array(new InputArgument('argument', InputArgument::REQUIRED), new InputArgument('argument', InputArgument::OPTIONAL)),
            array(new InputArgument('argument', InputArgument::REQUIRED), new InputArgument('argument', InputArgument::MULTI_VALUED)),
            array(new InputArgument('argument', InputArgument::OPTIONAL), new InputArgument('argument', InputArgument::MULTI_VALUED)),
            array(new InputArgument('argument', InputArgument::OPTIONAL, '', null), new InputArgument('argument', InputArgument::OPTIONAL, '', 'null')),
            array(new InputArgument('argument', InputArgument::OPTIONAL, '', 1), new InputArgument('argument', InputArgument::OPTIONAL, '', '1')),
            array(new InputArgument('argument', InputArgument::OPTIONAL, '', 'foo'), new InputArgument('argument', InputArgument::OPTIONAL, '', 'bar')),
            array(new InputArgument('argument', InputArgument::MULTI_VALUED, '', array('foo')), new InputArgument('argument', InputArgument::MULTI_VALUED, '', array('bar'))),
        );
    }
}
