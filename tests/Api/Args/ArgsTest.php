<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Args;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\CommandName;
use Webmozart\Console\Api\Args\Format\CommandOption;
use Webmozart\Console\Api\Args\Format\Option;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgsTest extends PHPUnit_Framework_TestCase
{
    public function testGetCommandNames()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandName(new CommandName('add'))
            ->addArgument(new Argument('argument'))
            ->addOption(new Option('option'))
            ->getFormat();

        $args = new Args($format);

        $this->assertSame(array('server', 'add'), $args->getCommandNames());
    }

    public function testGetCommandOptions()
    {
        $format = ArgsFormat::build()
            ->addCommandOption(new CommandOption('server'))
            ->addCommandOption(new CommandOption('add', 'a', CommandOption::PREFER_SHORT_NAME))
            ->addArgument(new Argument('argument'))
            ->addOption(new Option('option'))
            ->getFormat();

        $args = new Args($format);

        $this->assertSame(array('server', 'add'), $args->getCommandOptions());
    }

    public function testGetOptionReturnsOptionValue()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::OPTIONAL_VALUE))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option', 'value');

        $this->assertSame('value', $args->getOption('option'));
        $this->assertSame('value', $args->getOption('o'));
    }

    public function testGetOptionReturnsTrueIfNoValueAccepted()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::NO_VALUE))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option');

        $this->assertTrue($args->getOption('option'));
        $this->assertTrue($args->getOption('o'));
    }

    public function testGetOptionReturnsFalseIfNoValueAcceptedAndNotSet()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::NO_VALUE))
            ->getFormat();

        $args = new Args($format);

        $this->assertFalse($args->getOption('option'));
        $this->assertFalse($args->getOption('o'));
    }

    public function testGetOptionReturnsDefaultValueIfNotSet()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::OPTIONAL_VALUE, null, 'default'))
            ->getFormat();

        $args = new Args($format);

        $this->assertSame('default', $args->getOption('option'));
        $this->assertSame('default', $args->getOption('o'));
    }

    public function testGetOptionPrefersSetNullOverDefaultValue()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::OPTIONAL_VALUE, null, 'default'))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option', null);

        $this->assertNull($args->getOption('option'));
        $this->assertNull($args->getOption('o'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testGetOptionFailsIfUndefinedOption()
    {
        $args = new Args(new ArgsFormat());

        $args->getOption('foobar');
    }

    public function testGetOptions()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option1'))
            ->addOption(new Option('option2', null, Option::OPTIONAL_VALUE))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option1');
        $args->setOption('option2', 'value');

        $this->assertSame(array(
            'option1' => true,
            'option2' => 'value',
        ), $args->getOptions());
    }

    public function testGetOptionsAlwaysReturnsOptionsByLongName()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::OPTIONAL_VALUE | Option::PREFER_SHORT_NAME))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option', 'value');

        $this->assertSame(array(
            'option' => 'value',
        ), $args->getOptions());
    }

    public function testGetOptionsIncludesDefaults()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option1', null, Option::OPTIONAL_VALUE))
            ->addOption(new Option('option2', null, Option::OPTIONAL_VALUE, null, 'default'))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option1', 'value');

        $this->assertSame(array(
            'option1' => 'value',
            'option2' => 'default',
        ), $args->getOptions());
    }

    public function testGetOptionsDoesNotIncludeDefaultsIfDisabled()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option1', null, Option::OPTIONAL_VALUE))
            ->addOption(new Option('option2', null, Option::OPTIONAL_VALUE, null, 'default'))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option1', 'value');

        $this->assertSame(array(
            'option1' => 'value',
        ), $args->getOptions(false));
    }

    public function testGetOptionsReturnsFalseForUnsetOptionsWithoutValues()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option1', null, Option::OPTIONAL_VALUE))
            ->addOption(new Option('option2', null, Option::NO_VALUE))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option1', 'value');

        $this->assertSame(array(
            'option1' => 'value',
            'option2' => false,
        ), $args->getOptions());
    }

    public function testGetOptionsExcludesUnsetOptionsWithoutValuesIfNoMergeDefault()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option1', null, Option::OPTIONAL_VALUE))
            ->addOption(new Option('option2', null, Option::NO_VALUE))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option1', 'value');

        $this->assertSame(array(
            'option1' => 'value',
        ), $args->getOptions(false));
    }

    public function testGetOptionsPrefersSetNullOverDefaultValue()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', null, Option::OPTIONAL_VALUE, null, 'default'))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option', null);

        $this->assertSame(array(
            'option' => null,
        ), $args->getOptions());
    }

    public function testSetOptionToFalse()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::NO_VALUE))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option', false);

        $this->assertFalse($args->getOption('option'));
        $this->assertFalse($args->getOption('o'));
    }

    public function testSetOptionIgnoresValueIfNoValueAccepted()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::NO_VALUE))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option', 'value');

        $this->assertTrue($args->getOption('option'));
        $this->assertTrue($args->getOption('o'));
    }

    public function testSetOptionCastsValueToConfiguredType()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::OPTIONAL_VALUE | Option::INTEGER))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option', '1');

        $this->assertSame(1, $args->getOption('option'));
        $this->assertSame(1, $args->getOption('o'));
    }

    public function testSetMultiValuedOption()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::REQUIRED_VALUE | Option::INTEGER | Option::MULTI_VALUED))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option', array('1', '2'));

        $this->assertSame(array(1, 2), $args->getOption('option'));
        $this->assertSame(array(1, 2), $args->getOption('o'));
    }

    public function testSetOptionCastsValueToArrayIfMultiValued()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::REQUIRED_VALUE | Option::INTEGER | Option::MULTI_VALUED))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option', '1');

        $this->assertSame(array(1), $args->getOption('option'));
        $this->assertSame(array(1), $args->getOption('o'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testSetOptionFailsIfUndefinedOption()
    {
        $args = new Args(new ArgsFormat());

        $args->setOption('foobar');
    }

    public function testAddOptions()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option1', null, Option::NO_VALUE))
            ->addOption(new Option('option2', null, Option::OPTIONAL_VALUE))
            ->addOption(new Option('option3', null, Option::NO_VALUE))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option1');
        $args->addOptions(array(
            'option2' => 'value',
            'option3' => true,
        ));

        $this->assertSame(array(
            'option1' => true,
            'option2' => 'value',
            'option3' => true,
        ), $args->getOptions());
    }

    public function testSetOptions()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option1', null, Option::NO_VALUE))
            ->addOption(new Option('option2', null, Option::OPTIONAL_VALUE))
            ->addOption(new Option('option3', null, Option::NO_VALUE))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option1');
        $args->setOptions(array(
            'option2' => 'value',
            'option3' => true,
        ));

        $this->assertSame(array(
            'option2' => 'value',
            'option3' => true,
            'option1' => false,
        ), $args->getOptions());
    }

    public function testIsOptionSet()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option1', null, Option::NO_VALUE))
            ->addOption(new Option('option2', null, Option::OPTIONAL_VALUE))
            ->getFormat();

        $args = new Args($format);

        $this->assertFalse($args->isOptionSet('option1'));
        $this->assertFalse($args->isOptionSet('option2'));
        $this->assertFalse($args->isOptionSet('foo'));

        $args->setOption('option1');
        $args->setOption('option2', 'value');

        $this->assertTrue($args->isOptionSet('option1'));
        $this->assertTrue($args->isOptionSet('option2'));
        $this->assertFalse($args->isOptionSet('foo'));
    }

    public function testIsOptionSetReturnsFalseAfterSettingToFalse()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option'))
            ->getFormat();

        $args = new Args($format);
        $args->setOption('option');
        $args->setOption('option', false);

        $this->assertFalse($args->isOptionSet('option'));
    }

    public function testIsOptionDefined()
    {
        $format = ArgsFormat::build()
            ->addOption(new Option('option1', null, Option::NO_VALUE))
            ->addOption(new Option('option2', null, Option::OPTIONAL_VALUE))
            ->getFormat();

        $args = new Args($format);

        $this->assertTrue($args->isOptionDefined('option1'));
        $this->assertTrue($args->isOptionDefined('option2'));
        $this->assertFalse($args->isOptionSet('foo'));
    }

    public function testGetArgumentByName()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument1', 'value1');
        $args->setArgument('argument2', 'value2');

        $this->assertSame('value1', $args->getArgument('argument1'));
        $this->assertSame('value2', $args->getArgument('argument2'));
    }

    public function testGetArgumentByPosition()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument1', 'value1');
        $args->setArgument('argument2', 'value2');

        $this->assertSame('value1', $args->getArgument(0));
        $this->assertSame('value2', $args->getArgument(1));
    }

    public function testGetArgumentReturnsDefaultValueIfNotSet()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument', 0, null, 'default'))
            ->getFormat();

        $args = new Args($format);

        $this->assertSame('default', $args->getArgument('argument'));
    }

    public function testGetArgumentPrefersSetNullOverDefaultValue()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument', 0, null, 'default'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument', null);

        $this->assertNull($args->getArgument('argument'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchArgumentException
     * @expectedExceptionMessage foobar
     */
    public function testGetArgumentFailsIfUndefinedArgument()
    {
        $args = new Args(new ArgsFormat());

        $args->getArgument('foobar');
    }

    public function testGetArguments()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument1', 'value1');
        $args->setArgument('argument2', 'value2');

        $this->assertSame(array(
            'argument1' => 'value1',
            'argument2' => 'value2',
        ), $args->getArguments());
    }

    public function testGetArgumentsIncludesDefaultValues()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2', 0, null, 'default'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument1', 'value');

        $this->assertSame(array(
            'argument1' => 'value',
            'argument2' => 'default',
        ), $args->getArguments());
    }

    public function testGetArgumentsDoesNotIncludeDefaultValuesIfDisabled()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2', 0, null, 'default'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument1', 'value');

        $this->assertSame(array(
            'argument1' => 'value',
        ), $args->getArguments(false));
    }

    public function testGetArgumentsReturnsCorrectOrder()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument2', 'value2');
        $args->setArgument('argument1', 'value1');

        $this->assertSame(array(
            'argument1' => 'value1',
            'argument2' => 'value2',
        ), $args->getArguments());
    }

    public function testGetArgumentsPrefersSetNullOverDefaultValue()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument', 0, null, 'default'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument', null);

        $this->assertSame(array(
            'argument' => null,
        ), $args->getArguments());
    }

    public function testSetArgumentByPosition()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument(0, 'value1');
        $args->setArgument(1, 'value2');

        $this->assertSame('value1', $args->getArgument('argument1'));
        $this->assertSame('value2', $args->getArgument('argument2'));
    }

    public function testSetArgumentCastsValueToConfiguredType()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument', Argument::INTEGER))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument', '1');

        $this->assertSame(1, $args->getArgument('argument'));
    }

    public function testSetMultiValuedArgument()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument', Argument::MULTI_VALUED | Argument::INTEGER))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument', array('1', '2'));

        $this->assertSame(array(1, 2), $args->getArgument('argument'));
    }

    public function testSetArgumentCastsToArrayIfMultiValued()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument', Argument::MULTI_VALUED | Argument::INTEGER))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument', '1');

        $this->assertSame(array(1), $args->getArgument('argument'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchArgumentException
     * @expectedExceptionMessage foobar
     */
    public function testSetArgumentFailsIfUndefinedArgument()
    {
        $args = new Args(new ArgsFormat());

        $args->setArgument('foobar', 'value');
    }

    public function testAddArguments()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->addArgument(new Argument('argument3'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument1', 'value1');
        $args->addArguments(array(
            'argument2' => 'value2',
            'argument3' => 'value3',
        ));

        $this->assertSame(array(
            'argument1' => 'value1',
            'argument2' => 'value2',
            'argument3' => 'value3',
        ), $args->getArguments());
    }

    public function testSetArguments()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->addArgument(new Argument('argument3'))
            ->getFormat();

        $args = new Args($format);
        $args->setArgument('argument1', 'value1');
        $args->setArguments(array(
            'argument2' => 'value2',
            'argument3' => 'value3',
        ));

        $this->assertSame(array(
            'argument1' => null,
            'argument2' => 'value2',
            'argument3' => 'value3',
        ), $args->getArguments());
    }

    public function testIsArgumentSet()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->getFormat();

        $args = new Args($format);

        $this->assertFalse($args->isArgumentSet('argument1'));
        $this->assertFalse($args->isArgumentSet('argument2'));
        $this->assertFalse($args->isArgumentSet('foo'));

        $args->setArgument('argument1', 'value1');
        $args->setArgument('argument2', 'value2');

        $this->assertTrue($args->isArgumentSet('argument1'));
        $this->assertTrue($args->isArgumentSet('argument2'));
        $this->assertFalse($args->isArgumentSet('foo'));
    }

    public function testIsArgumentDefined()
    {
        $format = ArgsFormat::build()
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->getFormat();

        $args = new Args($format);

        $this->assertTrue($args->isArgumentDefined('argument1'));
        $this->assertTrue($args->isArgumentDefined('argument2'));
        $this->assertFalse($args->isArgumentDefined('foo'));
    }

    public function testGetFormat()
    {
        $format = new ArgsFormat();
        $args = new Args($format);

        $this->assertSame($format, $args->getFormat());
    }
}
