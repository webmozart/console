<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Args\Format;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\ArgsFormatBuilder;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\CommandName;
use Webmozart\Console\Api\Args\Format\CommandOption;
use Webmozart\Console\Api\Args\Format\Option;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgsFormatBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ArgsFormatBuilder
     */
    private $baseFormatBuilder;

    /**
     * @var ArgsFormat
     */
    private $baseFormat;

    /**
     * @var ArgsFormatBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->baseFormatBuilder = new ArgsFormatBuilder();
        $this->baseFormat = new ArgsFormat();
        $this->builder = new ArgsFormatBuilder($this->baseFormat);
    }

    public function testAddCommandName()
    {
        $this->builder->addCommandName($server = new CommandName('server'));
        $this->builder->addCommandName($add = new CommandName('add'));

        $this->assertSame(array($server, $add), $this->builder->getCommandNames());
    }

    public function testAddCommandNames()
    {
        $this->builder->addCommandName($cluster = new CommandName('cluster'));
        $this->builder->addCommandNames(array(
            $server = new CommandName('server'),
            $add = new CommandName('add'),
        ));

        $this->assertSame(array($cluster, $server, $add), $this->builder->getCommandNames());
    }

    public function testSetCommandNames()
    {
        $this->builder->addCommandName($cluster = new CommandName('cluster'));
        $this->builder->setCommandNames(array(
            $server = new CommandName('server'),
            $add = new CommandName('add'),
        ));

        $this->assertSame(array($server, $add), $this->builder->getCommandNames());
    }

    public function testHasCommandNames()
    {
        $this->assertFalse($this->builder->hasCommandNames());
        $this->assertFalse($this->builder->hasCommandNames(false));

        $this->builder->addCommandName(new CommandName('add'));

        $this->assertTrue($this->builder->hasCommandNames());
        $this->assertTrue($this->builder->hasCommandNames(false));
    }

    public function testHasCommandNamesWithBaseDefinition()
    {
        $this->baseFormatBuilder->addCommandName(new CommandName('server'));

        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertTrue($this->builder->hasCommandNames());
        $this->assertFalse($this->builder->hasCommandNames(false));

        $this->builder->addCommandName(new CommandName('add'));

        $this->assertTrue($this->builder->hasCommandNames());
        $this->assertTrue($this->builder->hasCommandNames(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandNamesFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasCommandNames(1234);
    }

    public function testGetCommandNames()
    {
        $this->builder->addCommandName($server = new CommandName('server'));
        $this->builder->addCommandName($add = new CommandName('add'));

        $this->assertSame(array($server, $add), $this->builder->getCommandNames());
        $this->assertSame(array($server, $add), $this->builder->getCommandNames(false));
    }

    public function testGetCommandNamesWithBaseDefinition()
    {
        $this->baseFormatBuilder->addCommandName($server = new CommandName('server'));

        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());
        $this->builder->addCommandName($add = new CommandName('add'));

        $this->assertSame(array($server, $add), $this->builder->getCommandNames());
        $this->assertSame(array($add), $this->builder->getCommandNames(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandNamesFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->getCommandNames(1234);
    }

    public function testAddCommandOption()
    {
        $this->builder->addCommandOption($option = new CommandOption('option'));

        $this->assertSame(array('option' => $option), $this->builder->getCommandOptions());
    }

    public function testAddCommandOptionPreservesExistingOptions()
    {
        $this->builder->addCommandOption($option1 = new CommandOption('option1'));
        $this->builder->addCommandOption($option2 = new CommandOption('option2'));

        $this->assertSame(array('option1' => $option1, 'option2' => $option2), $this->builder->getCommandOptions());
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingCommandOptionWithSameLongNameAsOtherCommandOption()
    {
        $this->builder->addCommandOption(new CommandOption('option', 'a'));
        $this->builder->addCommandOption(new CommandOption('option', 'b'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingCommandOptionWithSameLongNameAsOtherOption()
    {
        $this->builder->addOption(new Option('option', 'a'));
        $this->builder->addCommandOption(new CommandOption('option', 'b'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingCommandOptionWithSameShortNameAsOtherCommandOption()
    {
        $this->builder->addCommandOption(new CommandOption('option1', 'o'));
        $this->builder->addCommandOption(new CommandOption('option2', 'o'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingCommandOptionWithSameShortNameAsOtherOption()
    {
        $this->builder->addOption(new Option('option1', 'o'));
        $this->builder->addCommandOption(new CommandOption('option2', 'o'));
    }

    public function testAddCommandOptions()
    {
        $this->builder->addCommandOption($option1 = new CommandOption('option1'));
        $this->builder->addCommandOptions(array(
            $option2 = new CommandOption('option2'),
            $option3 = new CommandOption('option3'),
        ));

        $this->assertSame(array('option1' => $option1, 'option2' => $option2, 'option3' => $option3), $this->builder->getCommandOptions());
    }

    public function testSetCommandOptions()
    {
        $this->builder->addCommandOption($option1 = new CommandOption('option1'));
        $this->builder->setCommandOptions(array(
            $option2 = new CommandOption('option2'),
            $option3 = new CommandOption('option3'),
        ));

        $this->assertSame(array('option2' => $option2, 'option3' => $option3), $this->builder->getCommandOptions());
    }

    public function testGetCommandOptions()
    {
        $this->builder->addCommandOption($option1 = new CommandOption('option1'));
        $this->builder->addCommandOption($option2 = new CommandOption('option2'));

        $this->assertSame(array('option1' => $option1, 'option2' => $option2), $this->builder->getCommandOptions());
    }

    public function testGetCommandOptionsWithBaseDefinition()
    {
        $this->baseFormatBuilder->addCommandOption($option1 = new CommandOption('option1'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->addCommandOption($option2 = new CommandOption('option2'));
        $this->builder->addCommandOption($option3 = new CommandOption('option3'));

        $this->assertSame(array(
            'option1' => $option1,
            'option2' => $option2,
            'option3' => $option3,
        ), $this->builder->getCommandOptions());

        $this->assertSame(array(
            'option2' => $option2,
            'option3' => $option3,
        ), $this->builder->getCommandOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionsFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->getCommandOptions(1234);
    }

    public function testGetCommandOption()
    {
        $this->builder->addCommandOption($option = new CommandOption('option'));

        $this->assertSame($option, $this->builder->getCommandOption('option'));
    }

    public function testGetCommandOptionFromBaseDefinition()
    {
        $this->baseFormatBuilder->addCommandOption($option = new CommandOption('option'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertSame($option, $this->builder->getCommandOption('option'));
    }

    public function testGetCommandOptionByShortName()
    {
        $this->builder->addCommandOption($option = new CommandOption('option', 'o'));

        $this->assertSame($option, $this->builder->getCommandOption('o'));
    }

    public function testGetCommandOptionByShortNameFromBaseDefinition()
    {
        $this->baseFormatBuilder->addCommandOption($option = new CommandOption('option', 'o'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertSame($option, $this->builder->getCommandOption('o'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testGetCommandOptionFailsIfUnknownName()
    {
        $this->builder->getCommandOption('foobar');
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testGetCommandOptionFailsIfInBaseDefinitionButIncludeBaseDisabled()
    {
        $this->baseFormatBuilder->addCommandOption(new CommandOption('foobar'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->getCommandOption('foobar', false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionFailsIfNull()
    {
        $this->builder->getCommandOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionFailsIfEmpty()
    {
        $this->builder->getCommandOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionFailsIfNoString()
    {
        $this->builder->getCommandOption(1234);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->getCommandOption('argument', 1234);
    }

    public function testHasCommandOption()
    {
        $this->assertFalse($this->builder->hasCommandOption('option'));
        $this->assertFalse($this->builder->hasCommandOption('option', false));

        $this->builder->addCommandOption(new CommandOption('option'));

        $this->assertTrue($this->builder->hasCommandOption('option'));
        $this->assertTrue($this->builder->hasCommandOption('option', false));
    }

    public function testHasCommandOptionWithBaseDefinition()
    {
        $this->baseFormatBuilder->addCommandOption(new CommandOption('option1'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->addCommandOption(new CommandOption('option2'));

        $this->assertTrue($this->builder->hasCommandOption('option1'));
        $this->assertFalse($this->builder->hasCommandOption('option1', false));

        $this->assertTrue($this->builder->hasCommandOption('option2'));
        $this->assertTrue($this->builder->hasCommandOption('option2', false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionFailsIfNull()
    {
        $this->builder->hasCommandOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionFailsIfEmpty()
    {
        $this->builder->hasCommandOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionFailsIfNoString()
    {
        $this->builder->hasCommandOption(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasCommandOption('option', 1234);
    }

    public function testHasCommandOptions()
    {
        $this->assertFalse($this->builder->hasCommandOptions());
        $this->assertFalse($this->builder->hasCommandOptions(false));

        $this->builder->addCommandOption(new CommandOption('option'));

        $this->assertTrue($this->builder->hasCommandOptions());
        $this->assertTrue($this->builder->hasCommandOptions(false));
    }

    public function testHasCommandOptionsWithBaseDefinition()
    {
        $this->baseFormatBuilder->addCommandOption(new CommandOption('option'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertTrue($this->builder->hasCommandOptions());
        $this->assertFalse($this->builder->hasCommandOptions(false));

        $this->builder->addCommandOption(new CommandOption('option2'));

        $this->assertTrue($this->builder->hasCommandOptions());
        $this->assertTrue($this->builder->hasCommandOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionsFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasCommandOptions(1234);
    }

    public function testAddOptionalArgument()
    {
        $this->builder->addArgument($argument = new Argument('argument'));

        $this->assertSame(array('argument' => $argument), $this->builder->getArguments());
    }

    public function testAddRequiredArgument()
    {
        $this->builder->addArgument($argument = new Argument('argument', Argument::REQUIRED));

        $this->assertSame(array('argument' => $argument), $this->builder->getArguments());
    }

    public function testAddArgumentPreservesExistingArguments()
    {
        $this->builder->addArgument($argument1 = new Argument('argument1'));
        $this->builder->addArgument($argument2 = new Argument('argument2'));

        $this->assertSame(array('argument1' => $argument1, 'argument2' => $argument2), $this->builder->getArguments());
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingRequiredArgumentAfterOptionalArgument()
    {
        $this->builder->addArgument(new Argument('argument1', Argument::OPTIONAL));
        $this->builder->addArgument(new Argument('argument2', Argument::REQUIRED));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingRequiredArgumentAfterOptionalArgumentInBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument1', Argument::OPTIONAL));

        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());
        $this->builder->addArgument(new Argument('argument2', Argument::REQUIRED));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingRequiredArgumentAfterMultiValuedArgument()
    {
        $this->builder->addArgument(new Argument('argument1', Argument::MULTI_VALUED));
        $this->builder->addArgument(new Argument('argument2', Argument::REQUIRED));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingRequiredArgumentAfterMultiValuedArgumentInBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument1', Argument::MULTI_VALUED));

        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());
        $this->builder->addArgument(new Argument('argument2', Argument::REQUIRED));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingOptionalArgumentAfterMultiValuedArgument()
    {
        $this->builder->addArgument(new Argument('argument1', Argument::MULTI_VALUED));
        $this->builder->addArgument(new Argument('argument2', Argument::OPTIONAL));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingOptionalArgumentAfterMultiValuedArgumentInBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument1', Argument::MULTI_VALUED));

        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());
        $this->builder->addArgument(new Argument('argument2', Argument::OPTIONAL));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingArgumentWithExistingName()
    {
        $this->builder->addArgument(new Argument('argument', Argument::OPTIONAL));
        $this->builder->addArgument(new Argument('argument', Argument::REQUIRED));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingArgumentWithExistingNameInBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument', Argument::OPTIONAL));

        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());
        $this->builder->addArgument(new Argument('argument', Argument::REQUIRED));
    }

    public function testAddArguments()
    {
        $this->builder->addArgument($argument1 = new Argument('argument1'));
        $this->builder->addArguments(array(
            $argument2 = new Argument('argument2'),
            $argument3 = new Argument('argument3'),
        ));

        $this->assertSame(array('argument1' => $argument1, 'argument2' => $argument2, 'argument3' => $argument3), $this->builder->getArguments());
    }

    public function testSetArguments()
    {
        $this->builder->addArgument($argument1 = new Argument('argument1'));
        $this->builder->setArguments(array(
            $argument2 = new Argument('argument2'),
            $argument3 = new Argument('argument3'),
        ));

        $this->assertSame(array('argument2' => $argument2, 'argument3' => $argument3), $this->builder->getArguments());
    }

    public function testGetArgument()
    {
        $this->builder->addArgument($argument1 = new Argument('argument1'));
        $this->builder->addArgument($argument2 = new Argument('argument2'));

        $this->assertSame($argument1, $this->builder->getArgument('argument1'));
        $this->assertSame($argument2, $this->builder->getArgument('argument2'));
    }

    public function testGetArgumentFromBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument($argument = new Argument('argument'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertSame($argument, $this->builder->getArgument('argument'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchArgumentException
     * @expectedExceptionMessage foobar
     */
    public function testGetArgumentFailsIfUnknownName()
    {
        $this->builder->getArgument('foobar');
    }

    public function testGetArgumentByPosition()
    {
        $this->builder->addArgument($argument1 = new Argument('argument1'));
        $this->builder->addArgument($argument2 = new Argument('argument2'));

        $this->assertSame($argument1, $this->builder->getArgument(0));
        $this->assertSame($argument2, $this->builder->getArgument(1));
    }

    public function testGetArgumentByPositionFromBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument($argument = new Argument('argument'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertSame($argument, $this->builder->getArgument(0));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchArgumentException
     * @expectedExceptionMessage 0
     */
    public function testGetArgumentByPositionFailsIfUnknownPosition()
    {
        $this->builder->getArgument(0);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchArgumentException
     * @expectedExceptionMessage foobar
     */
    public function testGetArgumentFailsIfInBaseDefinitionButIncludeBaseDisabled()
    {
        $this->baseFormatBuilder->addArgument(new Argument('foobar'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->getArgument('foobar', false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfNull()
    {
        $this->builder->getArgument(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfEmpty()
    {
        $this->builder->getArgument('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfNoString()
    {
        $this->builder->getArgument(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->getArgument('argument', 1234);
    }

    public function testGetArguments()
    {
        $this->builder->addArgument($argument1 = new Argument('argument1'));
        $this->builder->addArgument($argument2 = new Argument('argument2'));

        $this->assertSame(array(
            'argument1' => $argument1,
            'argument2' => $argument2,
        ), $this->builder->getArguments());
    }

    public function testGetArgumentsWithBaseArguments()
    {
        $this->baseFormatBuilder->addArgument($argument1 = new Argument('argument1'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->addArgument($argument2 = new Argument('argument2'));

        $this->assertSame(array(
            'argument1' => $argument1,
            'argument2' => $argument2,
        ), $this->builder->getArguments());

        $this->assertSame(array(
            'argument2' => $argument2,
        ), $this->builder->getArguments(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->getArguments(1234);
    }

    public function testHasArgument()
    {
        $this->assertFalse($this->builder->hasArgument('argument'));
        $this->assertFalse($this->builder->hasArgument('argument', false));

        $this->builder->addArgument(new Argument('argument'));

        $this->assertTrue($this->builder->hasArgument('argument'));
        $this->assertTrue($this->builder->hasArgument('argument', false));
    }

    public function testHasArgumentWithBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument1'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->addArgument(new Argument('argument2'));

        $this->assertTrue($this->builder->hasArgument('argument1'));
        $this->assertFalse($this->builder->hasArgument('argument1', false));

        $this->assertTrue($this->builder->hasArgument('argument2'));
        $this->assertTrue($this->builder->hasArgument('argument2', false));
    }

    public function testHasArgumentAtPosition()
    {
        $this->assertFalse($this->builder->hasArgument(0));
        $this->assertFalse($this->builder->hasArgument(0, false));

        $this->builder->addArgument(new Argument('argument'));

        $this->assertTrue($this->builder->hasArgument(0));
        $this->assertTrue($this->builder->hasArgument(0, false));
    }

    public function testHasArgumentAtPositionWithBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument1'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->addArgument(new Argument('argument2'));

        $this->assertTrue($this->builder->hasArgument(0));
        $this->assertTrue($this->builder->hasArgument(1));

        $this->assertTrue($this->builder->hasArgument(0, false));
        $this->assertFalse($this->builder->hasArgument(1, false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfNull()
    {
        $this->builder->hasArgument(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfEmpty()
    {
        $this->builder->hasArgument('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfNoString()
    {
        $this->builder->hasArgument(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasArgument('argument', 1234);
    }

    public function testHasArguments()
    {
        $this->assertFalse($this->builder->hasArguments());
        $this->assertFalse($this->builder->hasArguments(false));

        $this->builder->addArgument(new Argument('argument'));

        $this->assertTrue($this->builder->hasArguments());
        $this->assertTrue($this->builder->hasArguments(false));
    }

    public function testHasArgumentsWithBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument1'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertTrue($this->builder->hasArguments());
        $this->assertFalse($this->builder->hasArguments(false));

        $this->builder->addArgument(new Argument('argument2'));

        $this->assertTrue($this->builder->hasArguments());
        $this->assertTrue($this->builder->hasArguments(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasArguments(1234);
    }

    public function testHasMultiValuedArgument()
    {
        $this->assertFalse($this->builder->hasMultiValuedArgument());
        $this->assertFalse($this->builder->hasMultiValuedArgument(false));

        $this->builder->addArgument(new Argument('argument1'));

        $this->assertFalse($this->builder->hasMultiValuedArgument());
        $this->assertFalse($this->builder->hasMultiValuedArgument(false));

        $this->builder->addArgument(new Argument('argument2', Argument::MULTI_VALUED));

        $this->assertTrue($this->builder->hasMultiValuedArgument());
        $this->assertTrue($this->builder->hasMultiValuedArgument(false));
    }

    public function testHasMultiValuedArgumentWithBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument', Argument::MULTI_VALUED));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertTrue($this->builder->hasMultiValuedArgument());
        $this->assertFalse($this->builder->hasMultiValuedArgument(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasMultiValuedArgumentFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasMultiValuedArgument(1234);
    }

    public function testHasOptionalArgument()
    {
        $this->assertFalse($this->builder->hasOptionalArgument());
        $this->assertFalse($this->builder->hasOptionalArgument(false));

        $this->builder->addArgument(new Argument('argument1', Argument::REQUIRED));

        $this->assertFalse($this->builder->hasOptionalArgument());
        $this->assertFalse($this->builder->hasOptionalArgument(false));

        $this->builder->addArgument(new Argument('argument2', Argument::OPTIONAL));

        $this->assertTrue($this->builder->hasOptionalArgument());
        $this->assertTrue($this->builder->hasOptionalArgument(false));
    }

    public function testHasOptionalArgumentWithBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument', Argument::OPTIONAL));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertTrue($this->builder->hasOptionalArgument());
        $this->assertFalse($this->builder->hasOptionalArgument(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionalArgumentFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasOptionalArgument(1234);
    }

    public function testHasRequiredArgument()
    {
        $this->assertFalse($this->builder->hasRequiredArgument());
        $this->assertFalse($this->builder->hasRequiredArgument(false));

        $this->builder->addArgument(new Argument('argument', Argument::REQUIRED));

        $this->assertTrue($this->builder->hasRequiredArgument());
        $this->assertTrue($this->builder->hasRequiredArgument(false));
    }

    public function testHasRequiredArgumentWithBaseDefinition()
    {
        $this->baseFormatBuilder->addArgument(new Argument('argument', Argument::REQUIRED));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertTrue($this->builder->hasRequiredArgument());
        $this->assertFalse($this->builder->hasRequiredArgument(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasRequiredArgumentFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasRequiredArgument(1234);
    }

    public function testAddOption()
    {
        $this->builder->addOption($option = new Option('option'));

        $this->assertSame(array('option' => $option), $this->builder->getOptions());
    }

    public function testAddOptionPreservesExistingOptions()
    {
        $this->builder->addOption($option1 = new Option('option1'));
        $this->builder->addOption($option2 = new Option('option2'));

        $this->assertSame(array('option1' => $option1, 'option2' => $option2), $this->builder->getOptions());
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingOptionWithSameLongNameAsOtherOption()
    {
        $this->builder->addOption(new Option('option', 'a'));
        $this->builder->addOption(new Option('option', 'b'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingOptionWithSameLongNameAsOtherCommandOption()
    {
        $this->builder->addCommandOption(new CommandOption('option', 'a'));
        $this->builder->addOption(new Option('option', 'b'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingOptionWithSameShortNameAsOtherOption()
    {
        $this->builder->addOption(new Option('option1', 'o'));
        $this->builder->addOption(new Option('option2', 'o'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailIfAddingOptionWithSameShortNameAsOtherCommandOption()
    {
        $this->builder->addCommandOption(new CommandOption('option1', 'o'));
        $this->builder->addOption(new Option('option2', 'o'));
    }

    public function testAddOptions()
    {
        $this->builder->addOption($option1 = new Option('option1'));
        $this->builder->addOptions(array(
            $option2 = new Option('option2'),
            $option3 = new Option('option3'),
        ));

        $this->assertSame(array('option1' => $option1, 'option2' => $option2, 'option3' => $option3), $this->builder->getOptions());
    }

    public function testSetOptions()
    {
        $this->builder->addOption($option1 = new Option('option1'));
        $this->builder->setOptions(array(
            $option2 = new Option('option2'),
            $option3 = new Option('option3'),
        ));

        $this->assertSame(array('option2' => $option2, 'option3' => $option3), $this->builder->getOptions());
    }

    public function testGetOptions()
    {
        $this->builder->addOption($option1 = new Option('option1'));
        $this->builder->addOption($option2 = new Option('option2'));

        $this->assertSame(array('option1' => $option1, 'option2' => $option2), $this->builder->getOptions());
    }

    public function testGetOptionsWithBaseDefinition()
    {
        $this->baseFormatBuilder->addOption($option1 = new Option('option1'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->addOption($option2 = new Option('option2'));
        $this->builder->addOption($option3 = new Option('option3'));

        $this->assertSame(array(
            'option2' => $option2,
            'option3' => $option3,
            'option1' => $option1,
        ), $this->builder->getOptions());

        $this->assertSame(array(
            'option2' => $option2,
            'option3' => $option3,
        ), $this->builder->getOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionsFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->getOptions(1234);
    }

    public function testGetOption()
    {
        $this->builder->addOption($option = new Option('option'));

        $this->assertSame($option, $this->builder->getOption('option'));
    }

    public function testGetOptionFromBaseDefinition()
    {
        $this->baseFormatBuilder->addOption($option = new Option('option'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertSame($option, $this->builder->getOption('option'));
    }

    public function testGetOptionByShortName()
    {
        $this->builder->addOption($option = new Option('option', 'o'));

        $this->assertSame($option, $this->builder->getOption('o'));
    }

    public function testGetOptionByShortNameFromBaseDefinition()
    {
        $this->baseFormatBuilder->addOption($option = new Option('option', 'o'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertSame($option, $this->builder->getOption('o'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testGetOptionFailsIfUnknownName()
    {
        $this->builder->getOption('foobar');
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testGetOptionFailsIfInBaseDefinitionButIncludeBaseDisabled()
    {
        $this->baseFormatBuilder->addOption(new Option('foobar'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->getOption('foobar', false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfNull()
    {
        $this->builder->getOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfEmpty()
    {
        $this->builder->getOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfNoString()
    {
        $this->builder->getOption(1234);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->getOption('argument', 1234);
    }

    public function testHasOption()
    {
        $this->assertFalse($this->builder->hasOption('option'));
        $this->assertFalse($this->builder->hasOption('option', false));

        $this->builder->addOption(new Option('option'));

        $this->assertTrue($this->builder->hasOption('option'));
        $this->assertTrue($this->builder->hasOption('option', false));
    }

    public function testHasOptionWithBaseDefinition()
    {
        $this->baseFormatBuilder->addOption(new Option('option1'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->builder->addOption(new Option('option2'));

        $this->assertTrue($this->builder->hasOption('option1'));
        $this->assertFalse($this->builder->hasOption('option1', false));

        $this->assertTrue($this->builder->hasOption('option2'));
        $this->assertTrue($this->builder->hasOption('option2', false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfNull()
    {
        $this->builder->hasOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfEmpty()
    {
        $this->builder->hasOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfNoString()
    {
        $this->builder->hasOption(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasOption('option', 1234);
    }

    public function testHasOptions()
    {
        $this->assertFalse($this->builder->hasOptions());
        $this->assertFalse($this->builder->hasOptions(false));

        $this->builder->addOption(new Option('option'));

        $this->assertTrue($this->builder->hasOptions());
        $this->assertTrue($this->builder->hasOptions(false));
    }

    public function testHasOptionsWithBaseDefinition()
    {
        $this->baseFormatBuilder->addOption(new Option('option'));
        $this->builder = new ArgsFormatBuilder($this->baseFormatBuilder->getFormat());

        $this->assertTrue($this->builder->hasOptions());
        $this->assertFalse($this->builder->hasOptions(false));

        $this->builder->addOption(new Option('option2'));

        $this->assertTrue($this->builder->hasOptions());
        $this->assertTrue($this->builder->hasOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionsFailsIfIncludeBaseNoBoolean()
    {
        $this->builder->hasOptions(1234);
    }

    public function testGetDefinition()
    {
        $this->builder->addCommandName($server = new CommandName('server'));
        $this->builder->addArgument($argument = new Argument('argument'));
        $this->builder->addOption($option = new Option('option'));

        $definition = $this->builder->getFormat();

        $this->assertSame($this->baseFormat, $definition->getBaseFormat());
        $this->assertSame(array($server), $definition->getCommandNames());
        $this->assertSame(array('argument' => $argument), $definition->getArguments());
        $this->assertSame(array('option' => $option), $definition->getOptions());
    }

    public function testGetDefinitionWithBaseDefinition()
    {
        $this->baseFormatBuilder->addCommandName($server = new CommandName('server'));
        $this->baseFormatBuilder->addArgument($argument1 = new Argument('argument1'));
        $this->baseFormatBuilder->addOption($option1 = new Option('option1'));
        $this->builder = new ArgsFormatBuilder($baseDefinition = $this->baseFormatBuilder->getFormat());

        $this->builder->addCommandName($add = new CommandName('add'));
        $this->builder->addArgument($argument2 = new Argument('argument2'));
        $this->builder->addArgument($argument3 = new Argument('argument3'));
        $this->builder->addOption($option2 = new Option('option2'));
        $this->builder->addOption($option3 = new Option('option3'));

        $definition = $this->builder->getFormat();

        $this->assertSame($baseDefinition, $definition->getBaseFormat());

        // base command names are returned first
        $this->assertSame(array($server, $add), $definition->getCommandNames());

        // base arguments are returned first
        $this->assertSame(array(
            'argument1' => $argument1,
            'argument2' => $argument2,
            'argument3' => $argument3,
        ), $definition->getArguments());

        // base options are returned last
        $this->assertSame(array(
            'option2' => $option2,
            'option3' => $option3,
            'option1' => $option1,
        ), $definition->getOptions());
    }

    public function testBuildEmptyDefinition()
    {
        $definition = $this->builder->getFormat();

        $this->assertSame($this->baseFormat, $definition->getBaseFormat());
        $this->assertCount(0, $definition->getArguments());
        $this->assertCount(0, $definition->getOptions());
    }
}
