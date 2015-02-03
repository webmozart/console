<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Command;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Input\InputOption;
use Webmozart\Console\Handler\CallableHandler;
use Webmozart\Console\Handler\RunnableHandler;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Command
     */
    private $command;

    protected function setUp()
    {
        $this->command = new Command('command');
    }

    public function testCreate()
    {
        $command = new Command();

        $this->assertNull($command->getName());
        $this->assertNull($command->getDescription());
        $this->assertNull($command->getHelp());
        $this->assertNull($command->getProcessTitle());
        $this->assertSame(array(), $command->getAliases());
    }

    public function testStaticCreate()
    {
        $command = Command::create();

        $this->assertNull($command->getName());
        $this->assertNull($command->getDescription());
        $this->assertNull($command->getHelp());
        $this->assertNull($command->getProcessTitle());
        $this->assertSame(array(), $command->getAliases());
    }

    public function testCreateWithName()
    {
        $command = new Command('command');

        $this->assertSame('command', $command->getName());
    }

    public function testStaticCreateWithName()
    {
        $command = Command::create('command');

        $this->assertSame('command', $command->getName());
    }

    public function testFreeze()
    {
        $this->assertFalse($this->command->isFrozen());

        $this->command->freeze();

        $this->assertTrue($this->command->isFrozen());
    }

    /**
     * @expectedException \LogicException
     */
    public function testFreezeFailsIfNoNameSet()
    {
        $command = new Command();
        $command->freeze();
    }

    /**
     * @expectedException \LogicException
     */
    public function testFreezeFailsIfCalledTwice()
    {
        $this->command->freeze();
        $this->command->freeze();
    }

    /**
     * @dataProvider getValidNames
     */
    public function testSetName($name)
    {
        $this->command->setName($name);

        $this->assertSame($name, $this->command->getName());
    }

    public function getValidNames()
    {
        return array(
            array('command'),
            array('command-name'),
            array('CommandName'),
            array('c'),
            array('command1'),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getInvalidNames
     */
    public function testSetNameFailsIfInvalid($name)
    {
        $this->command->setName($name);
    }

    public function getInvalidNames()
    {
        return array(
            array(null),
            array(1234),
            array(true),
            array(''),
            array('command_name'),
            array('command&'),
            array('command:name'),
            array('command name'),
        );
    }

    public function testSetNameOverwritesPreviousName()
    {
        $this->command->setName('command');
        $this->command->setName('changed');

        $this->assertSame('changed', $this->command->getName());
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetNameFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->setName('command');
    }

    public function testSetDescription()
    {
        $this->command->setDescription('Description');

        $this->assertSame('Description', $this->command->getDescription());
    }

    public function testSetDescriptionNull()
    {
        $this->command->setDescription(null);

        $this->assertNull($this->command->getDescription());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDescriptionFailsIfEmpty()
    {
        $this->command->setDescription('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDescriptionFailsIfNotString()
    {
        $this->command->setDescription(1234);
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetDescriptionFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->setDescription('Description');
    }

    public function testSetHelp()
    {
        $this->command->setHelp('Help');

        $this->assertSame('Help', $this->command->getHelp());
    }

    public function testSetHelpNull()
    {
        $this->command->setHelp(null);

        $this->assertNull($this->command->getHelp());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHelpFailsIfEmpty()
    {
        $this->command->setHelp('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHelpFailsIfNotString()
    {
        $this->command->setHelp(1234);
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetHelpFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->setHelp('Help');
    }

    public function testDisable()
    {
        $this->assertTrue($this->command->isEnabled());

        $this->command->disable();

        $this->assertFalse($this->command->isEnabled());
    }

    /**
     * @expectedException \LogicException
     */
    public function testDisableFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->disable();
    }

    public function testDisableIf()
    {
        $this->command->disableIf(true);
        $this->assertFalse($this->command->isEnabled());

        $this->command->disableIf(false);
        $this->assertTrue($this->command->isEnabled());
    }

    /**
     * @expectedException \LogicException
     */
    public function testDisableIfFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->disableIf(true);
    }

    public function testEnable()
    {
        $this->assertTrue($this->command->isEnabled());

        $this->command->disable();
        $this->command->enable();

        $this->assertTrue($this->command->isEnabled());
    }

    /**
     * @expectedException \LogicException
     */
    public function testEnableFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->enable();
    }

    public function testEnableIf()
    {
        $this->command->enableIf(true);
        $this->assertTrue($this->command->isEnabled());

        $this->command->enableIf(false);
        $this->assertFalse($this->command->isEnabled());
    }

    /**
     * @expectedException \LogicException
     */
    public function testEnableIfFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->enableIf(true);
    }

    public function testAddAlias()
    {
        $this->command->addAlias('alias1');
        $this->command->addAlias('alias2');

        $this->assertSame(array('alias1', 'alias2'), $this->command->getAliases());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasFailsIfNull()
    {
        $this->command->addAlias(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasFailsIfEmpty()
    {
        $this->command->addAlias('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasFailsIfNoString()
    {
        $this->command->addAlias(1234);
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddAliasFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->addAlias('alias');
    }

    public function testAddAliases()
    {
        $this->command->addAlias('alias1');
        $this->command->addAliases(array('alias2', 'alias3'));

        $this->assertSame(array('alias1', 'alias2', 'alias3'), $this->command->getAliases());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasesFailsIfNull()
    {
        $this->command->addAliases(array(null));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasesFailsIfEmpty()
    {
        $this->command->addAliases(array(''));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasesFailsIfNoString()
    {
        $this->command->addAliases(array(1234));
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddAliasesFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->addAliases(array('alias'));
    }

    public function testSetAliases()
    {
        $this->command->addAlias('alias1');
        $this->command->setAliases(array('alias2', 'alias3'));

        $this->assertSame(array('alias2', 'alias3'), $this->command->getAliases());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetAliasesFailsIfNull()
    {
        $this->command->setAliases(array(null));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetAliasesFailsIfEmpty()
    {
        $this->command->setAliases(array(''));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetAliasesFailsIfNoString()
    {
        $this->command->setAliases(array(1234));
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetAliasesFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->setAliases(array('alias'));
    }

    public function testSetProcessTitle()
    {
        $this->command->setProcessTitle('title');

        $this->assertSame('title', $this->command->getProcessTitle());
    }

    public function testSetProcessTitleNull()
    {
        $this->command->setProcessTitle(null);

        $this->assertNull($this->command->getProcessTitle());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetProcessTitleFailsIfEmpty()
    {
        $this->command->setProcessTitle('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetProcessTitleFailsIfNotString()
    {
        $this->command->setProcessTitle(1234);
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetProcessTitleFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->setProcessTitle('title');
    }

    public function testAddArgument()
    {
        $this->command->addArgument('argument1', InputArgument::REQUIRED, 'Description 1');
        $this->command->addArgument('argument2', InputArgument::OPTIONAL, 'Description 2', 'Default');
        $this->command->freeze();

        $this->assertEquals(array(
            'argument1' => new InputArgument('argument1', InputArgument::REQUIRED, 'Description 1'),
            'argument2' => new InputArgument('argument2', InputArgument::OPTIONAL, 'Description 2', 'Default'),
        ), $this->command->getInputDefinition()->getArguments());
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddArgumentFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->addArgument('argument');
    }

    public function testAddOption()
    {
        $this->command->addOption('option1', 'o', InputOption::VALUE_REQUIRED, 'Description 1');
        $this->command->addOption('option2', 'p', InputOption::VALUE_OPTIONAL, 'Description 2', 'Default');
        $this->command->freeze();

        $this->assertEquals(array(
            'option1' => new InputOption('option1', 'o', InputOption::VALUE_REQUIRED, 'Description 1'),
            'option2' => new InputOption('option2', 'p', InputOption::VALUE_OPTIONAL, 'Description 2', 'Default'),
        ), $this->command->getInputDefinition()->getOptions());
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddOptionFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->addOption('option');
    }

    public function testSetBaseInputDefinition()
    {
        $baseDefinition = new InputDefinition();

        $this->command->setBaseInputDefinition($baseDefinition);
        $this->command->freeze();

        $this->assertSame($baseDefinition, $this->command->getInputDefinition()->getBaseDefinition());
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetBaseInputDefinitionFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->setBaseInputDefinition(new InputDefinition());
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetInputDefinitionFailsIfNotFrozen()
    {
        $this->command->getInputDefinition();
    }

    public function setCallback()
    {
        $callback = function () {};

        $this->command->setCallback($callback);
        $this->command->freeze();

        $this->assertEquals(new CallableHandler($callback), $this->command->getHandler());
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetCallbackFailsIfFrozen()
    {
        $this->command->freeze();
        $this->command->setCallback(function () {});
    }

    public function testGetHandler()
    {
        $this->command->freeze();

        $this->assertEquals(new RunnableHandler($this->command), $this->command->getHandler());
    }
}
