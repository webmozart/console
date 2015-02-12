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
use stdClass;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\CommandName;
use Webmozart\Console\Api\Args\Format\CommandOption;
use Webmozart\Console\Api\Args\Format\Option;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgsFormatTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $format = new ArgsFormat();

        $this->assertSame(array(), $format->getArguments());
        $this->assertSame(array(), $format->getOptions());
        $this->assertSame(0, $format->getNumberOfArguments());
        $this->assertSame(0, $format->getNumberOfRequiredArguments());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testCreateFailsIfNeitherOptionNorArgument()
    {
        new ArgsFormat(array(new stdClass()));
    }

    public function testCreateWithElements()
    {
        $format = new ArgsFormat(array(
            $server = new CommandName('server'),
            $add = new CommandOption('add', 'a'),
            $host = new Argument('host'),
            $port = new Option('port', 'p'),
        ));

        $this->assertSame(array($server), $format->getCommandNames());
        $this->assertSame(array('add' => $add), $format->getCommandOptions());
        $this->assertSame(array('host' => $host), $format->getArguments());
        $this->assertSame(array('port' => $port), $format->getOptions());
        $this->assertSame(1, $format->getNumberOfArguments());
        $this->assertSame(0, $format->getNumberOfRequiredArguments());
    }

    public function testBuild()
    {
        $format = ArgsFormat::build()
            ->addCommandName($server = new CommandName('server'))
            ->addCommandOption($add = new CommandOption('add', 'a'))
            ->addArgument($host = new Argument('host'))
            ->addOption($port = new Option('port', 'p'))
            ->getFormat();

        $this->assertSame(array($server), $format->getCommandNames());
        $this->assertSame(array('add' => $add), $format->getCommandOptions());
        $this->assertSame(array('host' => $host), $format->getArguments());
        $this->assertSame(array('port' => $port), $format->getOptions());
        $this->assertSame(1, $format->getNumberOfArguments());
        $this->assertSame(0, $format->getNumberOfRequiredArguments());
    }

    public function testGetCommandNames()
    {
        $format = new ArgsFormat(array(
            $server = new CommandName('server'),
            $add = new CommandName('add'),
        ));

        $this->assertSame(array($server, $add), $format->getCommandNames());
        $this->assertSame(array($server, $add), $format->getCommandNames(false));
    }

    public function testGetCommandNamesWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $cluster = new CommandName('cluster'),
        ));

        $format = new ArgsFormat(array(
            $server = new CommandName('server'),
            $add = new CommandName('add'),
        ), $baseFormat);

        $this->assertSame(array($cluster, $server, $add), $format->getCommandNames());
        $this->assertSame(array($server, $add), $format->getCommandNames(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandNamesFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->getCommandNames(1234);
    }

    public function testHasCommandNames()
    {
        $format = new ArgsFormat(array(
            new CommandName('add'),
        ));

        $this->assertTrue($format->hasCommandNames());
        $this->assertTrue($format->hasCommandNames(false));
    }

    public function testHasCommandNamesWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            new CommandName('add'),
        ));

        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertTrue($format->hasCommandNames());
        $this->assertFalse($format->hasCommandNames(false));
    }

    public function testHasNoCommandNames()
    {
        $format = new ArgsFormat();

        $this->assertFalse($format->hasCommandNames());
        $this->assertFalse($format->hasCommandNames(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandNamesFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasCommandNames(1234);
    }

    public function testGetArgument()
    {
        $format = new ArgsFormat(array(
            $argument1 = new Argument('argument1'),
            $argument2 = new Argument('argument2'),
        ));

        $this->assertSame($argument1, $format->getArgument('argument1'));
        $this->assertSame($argument2, $format->getArgument('argument2'));
    }

    public function testGetArgumentFromBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $argument = new Argument('argument'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertSame($argument, $format->getArgument('argument'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchArgumentException
     * @expectedExceptionMessage foobar
     */
    public function testGetArgumentFailsIfUnknownName()
    {
        $format = new ArgsFormat();
        $format->getArgument('foobar');
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchArgumentException
     * @expectedExceptionMessage foobar
     */
    public function testGetArgumentFailsIfInBaseFormatButIncludeBaseDisabled()
    {
        $baseFormat = new ArgsFormat(array(
            $argument = new Argument('foobar'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $format->getArgument('foobar', false);
    }

    public function testGetArgumentByPosition()
    {
        $format = new ArgsFormat(array(
            $argument1 = new Argument('argument1'),
            $argument2 = new Argument('argument2'),
        ));

        $this->assertSame($argument1, $format->getArgument(0));
        $this->assertSame($argument2, $format->getArgument(1));
    }

    public function testGetArgumentByPositionFromBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $argument = new Argument('argument'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertSame($argument, $format->getArgument(0));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchArgumentException
     * @expectedExceptionMessage 0
     */
    public function testGetArgumentByPositionFailsIfUnknownPosition()
    {
        $format = new ArgsFormat();
        $format->getArgument(0);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchArgumentException
     * @expectedExceptionMessage 0
     */
    public function testGetArgumentByPositionFailsIfInBaseFormatButIncludeBaseDisabled()
    {
        $baseFormat = new ArgsFormat(array(
            $argument = new Argument('argument'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $format->getArgument(0, false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfNull()
    {
        $format = new ArgsFormat();
        $format->getArgument(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfEmpty()
    {
        $format = new ArgsFormat();
        $format->getArgument('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfNoString()
    {
        $format = new ArgsFormat();
        $format->getArgument(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->getArgument('argument', 1234);
    }

    public function testGetArguments()
    {
        $format = new ArgsFormat(array(
            $argument1 = new Argument('argument1'),
            $argument2 = new Argument('argument2'),
        ));

        $this->assertSame(array(
            'argument1' => $argument1,
            'argument2' => $argument2,
        ), $format->getArguments());
    }

    public function testGetArgumentsWithBaseArguments()
    {
        $baseFormat = new ArgsFormat(array(
            $argument1 = new Argument('argument1'),
        ));
        $format = new ArgsFormat(array(
            $argument2 = new Argument('argument2'),
        ), $baseFormat);

        $this->assertSame(array(
            'argument1' => $argument1,
            'argument2' => $argument2,
        ), $format->getArguments());

        $this->assertSame(array(
            'argument2' => $argument2,
        ), $format->getArguments(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->getArguments(1234);
    }

    public function testHasArgument()
    {
        $format = new ArgsFormat();

        $this->assertFalse($format->hasArgument('argument'));
        $this->assertFalse($format->hasArgument('argument', false));

        $format = new ArgsFormat(array(
            new Argument('argument'),
        ));

        $this->assertTrue($format->hasArgument('argument'));
        $this->assertTrue($format->hasArgument('argument', false));
    }

    public function testHasArgumentWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $argument1 = new Argument('argument1'),
        ));
        $format = new ArgsFormat(array(
            $argument2 = new Argument('argument2'),
        ), $baseFormat);

        $this->assertTrue($format->hasArgument('argument1'));
        $this->assertTrue($format->hasArgument('argument2'));

        $this->assertFalse($format->hasArgument('argument1', false));
        $this->assertTrue($format->hasArgument('argument2', false));
    }

    public function testHasArgumentAtPosition()
    {
        $format = new ArgsFormat();

        $this->assertFalse($format->hasArgument(0));
        $this->assertFalse($format->hasArgument(0, false));

        $format = new ArgsFormat(array(
            new Argument('argument'),
        ));

        $this->assertTrue($format->hasArgument(0));
        $this->assertTrue($format->hasArgument(0, false));
    }

    public function testHasArgumentAtPositionWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            new Argument('argument1'),
        ));
        $format = new ArgsFormat(array(
            new Argument('argument2'),
        ), $baseFormat);

        $this->assertTrue($format->hasArgument(0));
        $this->assertTrue($format->hasArgument(1));

        $this->assertTrue($format->hasArgument(0, false));
        $this->assertFalse($format->hasArgument(1, false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfNull()
    {
        $format = new ArgsFormat();
        $format->hasArgument(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfEmpty()
    {
        $format = new ArgsFormat();
        $format->hasArgument('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfNoString()
    {
        $format = new ArgsFormat();
        $format->hasArgument(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasArgument('argument', 1234);
    }

    public function testHasArguments()
    {
        $format = new ArgsFormat();
        $this->assertFalse($format->hasArguments());
        $this->assertFalse($format->hasArguments(false));

        $format = new ArgsFormat(array(
            new Argument('argument'),
        ));
        $this->assertTrue($format->hasArguments());
        $this->assertTrue($format->hasArguments(false));
    }

    public function testHasArgumentsWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            new Argument('argument'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertTrue($format->hasArguments());
        $this->assertFalse($format->hasArguments(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasArguments(1234);
    }

    public function testHasMultiValuedArgument()
    {
        $format = new ArgsFormat();
        $this->assertFalse($format->hasMultiValuedArgument());
        $this->assertFalse($format->hasMultiValuedArgument(false));

        $format = new ArgsFormat(array(new Argument('argument')));
        $this->assertFalse($format->hasMultiValuedArgument());
        $this->assertFalse($format->hasMultiValuedArgument(false));

        $format = new ArgsFormat(array(new Argument('argument', Argument::MULTI_VALUED)));
        $this->assertTrue($format->hasMultiValuedArgument());
        $this->assertTrue($format->hasMultiValuedArgument(false));
    }

    public function testHasMultiValuedArgumentWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            new Argument('argument', Argument::MULTI_VALUED),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertTrue($format->hasMultiValuedArgument());
        $this->assertFalse($format->hasMultiValuedArgument(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasMultiValuedArgumentFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasMultiValuedArgument(1234);
    }

    public function testHasOptionalArgument()
    {
        $format = new ArgsFormat();
        $this->assertFalse($format->hasOptionalArgument());
        $this->assertFalse($format->hasOptionalArgument(false));

        $format = new ArgsFormat(array(new Argument('argument', Argument::REQUIRED)));
        $this->assertFalse($format->hasOptionalArgument());
        $this->assertFalse($format->hasOptionalArgument(false));

        $format = new ArgsFormat(array(new Argument('argument', Argument::OPTIONAL)));
        $this->assertTrue($format->hasOptionalArgument());
        $this->assertTrue($format->hasOptionalArgument(false));
    }

    public function testHasOptionalArgumentWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            new Argument('argument', Argument::OPTIONAL),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertTrue($format->hasOptionalArgument());
        $this->assertFalse($format->hasOptionalArgument(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionalArgumentFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasOptionalArgument(1234);
    }

    public function testHasRequiredArgument()
    {
        $format = new ArgsFormat();
        $this->assertFalse($format->hasRequiredArgument());
        $this->assertFalse($format->hasRequiredArgument(false));

        $format = new ArgsFormat(array(new Argument('argument', Argument::OPTIONAL)));
        $this->assertFalse($format->hasRequiredArgument());
        $this->assertFalse($format->hasRequiredArgument(false));

        $format = new ArgsFormat(array(new Argument('argument', Argument::REQUIRED)));
        $this->assertTrue($format->hasRequiredArgument());
        $this->assertTrue($format->hasRequiredArgument(false));
    }

    public function testHasRequiredArgumentWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            new Argument('argument', Argument::REQUIRED),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertTrue($format->hasRequiredArgument());
        $this->assertFalse($format->hasRequiredArgument(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasRequiredArgumentFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasRequiredArgument(1234);
    }

    public function testGetNumberOfArguments()
    {
        $format = new ArgsFormat();

        $this->assertSame(0, $format->getNumberOfArguments());

        $format = new ArgsFormat(array(
            new Argument('argument1'),
            new Argument('argument2'),
        ));

        $this->assertSame(2, $format->getNumberOfArguments());
    }

    public function testGetNumberOfArgumentsWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            new Argument('argument1'),
        ));
        $format = new ArgsFormat(array(
            new Argument('argument2'),
        ), $baseFormat);

        $this->assertSame(2, $format->getNumberOfArguments());
        $this->assertSame(1, $format->getNumberOfArguments(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNumberOfArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->getNumberOfArguments(1234);
    }

    public function testGetNumberOfRequiredArguments()
    {
        $format = new ArgsFormat();

        $this->assertSame(0, $format->getNumberOfRequiredArguments());

        $format = new ArgsFormat(array(
            new Argument('argument1', Argument::REQUIRED),
            new Argument('argument2', Argument::REQUIRED),
            new Argument('argument3'),
        ));

        $this->assertSame(2, $format->getNumberOfRequiredArguments());
    }

    public function testGetNumberOfRequiredArgumentsWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            new Argument('argument1', Argument::REQUIRED),
        ));
        $format = new ArgsFormat(array(
            new Argument('argument2', Argument::REQUIRED),
            new Argument('argument3'),
        ), $baseFormat);

        $this->assertSame(2, $format->getNumberOfRequiredArguments());
        $this->assertSame(1, $format->getNumberOfRequiredArguments(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNumberOfRequiredArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->getNumberOfRequiredArguments(1234);
    }

    public function testGetCommandOptions()
    {
        $format = new ArgsFormat(array(
            $option1 = new CommandOption('option1'),
            $option2 = new CommandOption('option2'),
        ));

        $this->assertSame(array('option1' => $option1, 'option2' => $option2), $format->getCommandOptions());
    }

    public function testGetCommandOptionsWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option1 = new CommandOption('option1'),
        ));
        $format = new ArgsFormat(array(
            $option2 = new CommandOption('option2'),
            $option3 = new CommandOption('option3'),
        ), $baseFormat);

        $this->assertSame(array(
            'option1' => $option1,
            'option2' => $option2,
            'option3' => $option3,
        ), $format->getCommandOptions());

        $this->assertSame(array(
            'option2' => $option2,
            'option3' => $option3,
        ), $format->getCommandOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionsFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->getCommandOptions(1234);
    }

    public function testGetCommandOption()
    {
        $format = new ArgsFormat(array(
            $option = new CommandOption('option'),
        ));

        $this->assertSame($option, $format->getCommandOption('option'));
    }

    public function testGetCommandOptionFromBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option = new CommandOption('option'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertSame($option, $format->getCommandOption('option'));
    }

    public function testGetCommandOptionByShortName()
    {
        $format = new ArgsFormat(array(
            $option = new CommandOption('option', 'o'),
        ));

        $this->assertSame($option, $format->getCommandOption('o'));
    }

    public function testGetCommandOptionByShortNameFromBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option = new CommandOption('option', 'o'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertSame($option, $format->getCommandOption('o'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testGetCommandOptionFailsIfUnknownName()
    {
        $format = new ArgsFormat();
        $format->getCommandOption('foobar');
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testGetCommandOptionFailsIfInBaseFormatButIncludeBaseDisabled()
    {
        $baseFormat = new ArgsFormat(array(
            $option = new CommandOption('foobar'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $format->getCommandOption('foobar', false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionFailsIfNull()
    {
        $format = new ArgsFormat();
        $format->getCommandOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionFailsIfEmpty()
    {
        $format = new ArgsFormat();
        $format->getCommandOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionFailsIfNoString()
    {
        $format = new ArgsFormat();
        $format->getCommandOption(1234);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandOptionFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->getCommandOption('argument', 1234);
    }

    public function testHasCommandOption()
    {
        $format = new ArgsFormat();
        $this->assertFalse($format->hasCommandOption('option'));
        $this->assertFalse($format->hasCommandOption('option', false));

        $format = new ArgsFormat(array(new CommandOption('option')));
        $this->assertTrue($format->hasCommandOption('option'));
        $this->assertTrue($format->hasCommandOption('option', false));
    }

    public function testHasCommandOptionWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option1 = new CommandOption('option1'),
        ));
        $format = new ArgsFormat(array(
            $option2 = new CommandOption('option2'),
        ), $baseFormat);

        $this->assertTrue($format->hasCommandOption('option1'));
        $this->assertFalse($format->hasCommandOption('option1', false));

        $this->assertTrue($format->hasCommandOption('option2'));
        $this->assertTrue($format->hasCommandOption('option2', false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionFailsIfNull()
    {
        $format = new ArgsFormat();
        $format->hasCommandOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionFailsIfEmpty()
    {
        $format = new ArgsFormat();
        $format->hasCommandOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionFailsIfNoString()
    {
        $format = new ArgsFormat();
        $format->hasCommandOption(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasCommandOption('option', 1234);
    }

    public function testHasCommandOptions()
    {
        $format = new ArgsFormat();
        $this->assertFalse($format->hasCommandOptions());
        $this->assertFalse($format->hasCommandOptions(false));

        $format = new ArgsFormat(array(new CommandOption('option')));
        $this->assertTrue($format->hasCommandOptions());
        $this->assertTrue($format->hasCommandOptions(false));
    }

    public function testHasCommandOptionsWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option = new CommandOption('option'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertTrue($format->hasCommandOptions());
        $this->assertFalse($format->hasCommandOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasCommandOptionsFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasCommandOptions(1234);
    }

    public function testGetOptions()
    {
        $format = new ArgsFormat(array(
            $option1 = new Option('option1'),
            $option2 = new Option('option2'),
        ));

        $this->assertSame(array('option1' => $option1, 'option2' => $option2), $format->getOptions());
    }

    public function testGetOptionsWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option1 = new Option('option1'),
        ));
        $format = new ArgsFormat(array(
            $option2 = new Option('option2'),
            $option3 = new Option('option3'),
        ), $baseFormat);

        $this->assertSame(array(
            'option2' => $option2,
            'option3' => $option3,
            'option1' => $option1,
        ), $format->getOptions());

        $this->assertSame(array(
            'option2' => $option2,
            'option3' => $option3,
        ), $format->getOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionsFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->getOptions(1234);
    }

    public function testGetOption()
    {
        $format = new ArgsFormat(array(
            $option = new Option('option'),
        ));

        $this->assertSame($option, $format->getOption('option'));
    }

    public function testGetOptionFromBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option = new Option('option'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertSame($option, $format->getOption('option'));
    }

    public function testGetOptionByShortName()
    {
        $format = new ArgsFormat(array(
            $option = new Option('option', 'o'),
        ));

        $this->assertSame($option, $format->getOption('o'));
    }

    public function testGetOptionByShortNameFromBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option = new Option('option', 'o'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertSame($option, $format->getOption('o'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testGetOptionFailsIfUnknownName()
    {
        $format = new ArgsFormat();
        $format->getOption('foobar');
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\NoSuchOptionException
     * @expectedExceptionMessage foobar
     */
    public function testGetOptionFailsIfInBaseFormatButIncludeBaseDisabled()
    {
        $baseFormat = new ArgsFormat(array(
            $option = new Option('foobar'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $format->getOption('foobar', false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfNull()
    {
        $format = new ArgsFormat();
        $format->getOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfEmpty()
    {
        $format = new ArgsFormat();
        $format->getOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfNoString()
    {
        $format = new ArgsFormat();
        $format->getOption(1234);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->getOption('argument', 1234);
    }

    public function testHasOption()
    {
        $format = new ArgsFormat();
        $this->assertFalse($format->hasOption('option'));
        $this->assertFalse($format->hasOption('option', false));

        $format = new ArgsFormat(array(new Option('option')));
        $this->assertTrue($format->hasOption('option'));
        $this->assertTrue($format->hasOption('option', false));
    }

    public function testHasOptionWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option1 = new Option('option1'),
        ));
        $format = new ArgsFormat(array(
            $option2 = new Option('option2'),
        ), $baseFormat);

        $this->assertTrue($format->hasOption('option1'));
        $this->assertFalse($format->hasOption('option1', false));

        $this->assertTrue($format->hasOption('option2'));
        $this->assertTrue($format->hasOption('option2', false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfNull()
    {
        $format = new ArgsFormat();
        $format->hasOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfEmpty()
    {
        $format = new ArgsFormat();
        $format->hasOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfNoString()
    {
        $format = new ArgsFormat();
        $format->hasOption(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasOption('option', 1234);
    }

    public function testHasOptions()
    {
        $format = new ArgsFormat();
        $this->assertFalse($format->hasOptions());
        $this->assertFalse($format->hasOptions(false));

        $format = new ArgsFormat(array(new Option('option')));
        $this->assertTrue($format->hasOptions());
        $this->assertTrue($format->hasOptions(false));
    }

    public function testHasOptionsWithBaseFormat()
    {
        $baseFormat = new ArgsFormat(array(
            $option = new Option('option'),
        ));
        $format = new ArgsFormat(array(), $baseFormat);

        $this->assertTrue($format->hasOptions());
        $this->assertFalse($format->hasOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionsFailsIfIncludeBaseNoBoolean()
    {
        $format = new ArgsFormat();
        $format->hasOptions(1234);
    }
}
