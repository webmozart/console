<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Adapter;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Console\Adapter\ArgsFormatInputDefinition;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\CommandName;
use Webmozart\Console\Api\Args\Format\CommandOption;
use Webmozart\Console\Api\Args\Format\Option;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgsFormatInputDefinitionTest extends PHPUnit_Framework_TestCase
{
    public function testAdaptCommandNames()
    {
        $argsFormat = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandName(new CommandName('add'))
            ->addArgument(new Argument('cmd2'))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(
            'cmd1' => new InputArgument('cmd1', InputArgument::REQUIRED),
            'cmd3' => new InputArgument('cmd3', InputArgument::REQUIRED),
            'cmd2' => new InputArgument('cmd2', InputArgument::OPTIONAL),
        ), $adapter->getArguments());
        $this->assertEquals(array(), $adapter->getOptions());
    }

    public function testAdaptCommandOptions()
    {
        $argsFormat = ArgsFormat::build()
            ->addCommandOption(new CommandOption('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(), $adapter->getArguments());
        $this->assertEquals(array(
            'server' => new InputOption('server'),
            'add' => new InputOption('add', 'a'),
        ), $adapter->getOptions());
    }

    public function testAdaptOptionalArgument()
    {
        $argsFormat = ArgsFormat::build()
            ->addArgument(new Argument('argument', Argument::OPTIONAL))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(
            'argument' => new InputArgument('argument', InputArgument::OPTIONAL),
        ), $adapter->getArguments());
        $this->assertEquals(array(), $adapter->getOptions());
    }

    public function testAdaptOptionalMultiValuedArgument()
    {
        $argsFormat = ArgsFormat::build()
            ->addArgument(new Argument('argument', Argument::OPTIONAL | Argument::MULTI_VALUED))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(
            'argument' => new InputArgument('argument', InputArgument::OPTIONAL | InputArgument::IS_ARRAY),
        ), $adapter->getArguments());
        $this->assertEquals(array(), $adapter->getOptions());
    }

    public function testAdaptRequiredArgument()
    {
        $argsFormat = ArgsFormat::build()
            ->addArgument(new Argument('argument', Argument::REQUIRED))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(
            'argument' => new InputArgument('argument', InputArgument::REQUIRED),
        ), $adapter->getArguments());
        $this->assertEquals(array(), $adapter->getOptions());
    }

    public function testAdaptRequiredMultiValuedArgument()
    {
        $argsFormat = ArgsFormat::build()
            ->addArgument(new Argument('argument', Argument::REQUIRED | Argument::MULTI_VALUED))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(
            'argument' => new InputArgument('argument', InputArgument::REQUIRED | InputArgument::IS_ARRAY),
        ), $adapter->getArguments());
        $this->assertEquals(array(), $adapter->getOptions());
    }

    public function testAdaptArgumentWithDescriptionAndDefault()
    {
        $argsFormat = ArgsFormat::build()
            ->addArgument(new Argument('argument', Argument::OPTIONAL, 'The description', 'The default'))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(
            'argument' => new InputArgument('argument', InputArgument::OPTIONAL, 'The description', 'The default'),
        ), $adapter->getArguments());
        $this->assertEquals(array(), $adapter->getOptions());
    }

    public function testAdaptOptionWithoutValue()
    {
        $argsFormat = ArgsFormat::build()
            ->addOption(new Option('option', null, Option::NO_VALUE))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(), $adapter->getArguments());
        $this->assertEquals(array(
            'option' => new InputOption('option', null, InputOption::VALUE_NONE),
        ), $adapter->getOptions());
    }

    public function testAdaptOptionWithOptionalValue()
    {
        $argsFormat = ArgsFormat::build()
            ->addOption(new Option('option', null, Option::OPTIONAL_VALUE))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(), $adapter->getArguments());
        $this->assertEquals(array(
            'option' => new InputOption('option', null, InputOption::VALUE_OPTIONAL),
        ), $adapter->getOptions());
    }

    public function testAdaptOptionWithRequiredValue()
    {
        $argsFormat = ArgsFormat::build()
            ->addOption(new Option('option', null, Option::REQUIRED_VALUE))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(), $adapter->getArguments());
        $this->assertEquals(array(
            'option' => new InputOption('option', null, InputOption::VALUE_REQUIRED),
        ), $adapter->getOptions());
    }

    public function testAdaptOptionWithRequiredMultiValuedValue()
    {
        $argsFormat = ArgsFormat::build()
            ->addOption(new Option('option', null, Option::REQUIRED_VALUE | Option::MULTI_VALUED))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(), $adapter->getArguments());
        $this->assertEquals(array(
            'option' => new InputOption('option', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
        ), $adapter->getOptions());
    }

    public function testAdaptOptionWithDescriptionAndDefault()
    {
        $argsFormat = ArgsFormat::build()
            ->addOption(new Option('option', 'o', Option::OPTIONAL_VALUE, 'The description', 'The default'))
            ->getFormat();

        $adapter = new ArgsFormatInputDefinition($argsFormat);

        $this->assertEquals(array(), $adapter->getArguments());
        $this->assertEquals(array(
            'option' => new InputOption('option', 'o', InputOption::VALUE_OPTIONAL, 'The description', 'The default'),
        ), $adapter->getOptions());
    }
}
