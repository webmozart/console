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
use stdClass;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandConfig;
use Webmozart\Console\Api\Command\OptionCommandConfig;
use Webmozart\Console\Api\Command\SubCommandConfig;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputOption;
use Webmozart\Console\Handler\NullHandler;
use Webmozart\Console\Tests\Api\Command\Fixtures\TestRunnableConfig;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CommandConfig
     */
    private $config;

    protected function setUp()
    {
        $this->config = new CommandConfig('command');
    }

    public function testCreate()
    {
        $config = new CommandConfig();

        $this->assertNull($config->getName());
        $this->assertNull($config->getDescription());
        $this->assertNull($config->getHelp());
        $this->assertNull($config->getProcessTitle());
        $this->assertNull($config->getCallback());
        $this->assertSame(array(), $config->getAliases());
        $this->assertSame(array(), $config->getArguments());
        $this->assertSame(array(), $config->getOptions());
        $this->assertSame(array(), $config->getSubCommandConfigs());
        $this->assertSame(array(), $config->getOptionCommandConfigs());
    }

    public function testCreateWithName()
    {
        $config = new CommandConfig('command');

        $this->assertSame('command', $config->getName());
    }

    public function testStaticCreate()
    {
        $config = CommandConfig::create();

        $this->assertNull($config->getName());
        $this->assertNull($config->getDescription());
        $this->assertNull($config->getHelp());
        $this->assertNull($config->getProcessTitle());
        $this->assertNull($config->getCallback());
        $this->assertSame(array(), $config->getAliases());
        $this->assertSame(array(), $config->getArguments());
        $this->assertSame(array(), $config->getOptions());
        $this->assertSame(array(), $config->getSubCommandConfigs());
        $this->assertSame(array(), $config->getOptionCommandConfigs());
    }

    public function testStaticCreateWithName()
    {
        $config = CommandConfig::create('command');

        $this->assertSame('command', $config->getName());
    }

    /**
     * @dataProvider getValidNames
     */
    public function testSetName($name)
    {
        $this->config->setName($name);

        $this->assertSame($name, $this->config->getName());
    }

    public function getValidNames()
    {
        return array(
            array('command'),
            array('command-name'),
            array('CommandName'),
            array('c'),
            array('cd'),
            array('command1'),
            array(null),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getInvalidNames
     */
    public function testSetNameFailsIfInvalid($name)
    {
        $this->config->setName($name);
    }

    public function getInvalidNames()
    {
        return array(
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
        $this->config->setName('command');
        $this->config->setName('changed');

        $this->assertSame('changed', $this->config->getName());
    }

    public function testSetDescription()
    {
        $this->config->setDescription('Description');

        $this->assertSame('Description', $this->config->getDescription());
    }

    public function testSetDescriptionNull()
    {
        $this->config->setDescription(null);

        $this->assertNull($this->config->getDescription());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDescriptionFailsIfEmpty()
    {
        $this->config->setDescription('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDescriptionFailsIfNotString()
    {
        $this->config->setDescription(1234);
    }

    public function testSetHelp()
    {
        $this->config->setHelp('Help');

        $this->assertSame('Help', $this->config->getHelp());
    }

    public function testSetHelpNull()
    {
        $this->config->setHelp(null);

        $this->assertNull($this->config->getHelp());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHelpFailsIfEmpty()
    {
        $this->config->setHelp('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHelpFailsIfNotString()
    {
        $this->config->setHelp(1234);
    }

    public function testDisable()
    {
        $this->assertTrue($this->config->isEnabled());

        $this->config->disable();

        $this->assertFalse($this->config->isEnabled());
    }

    public function testDisableIf()
    {
        $this->config->disableIf(true);
        $this->assertFalse($this->config->isEnabled());

        $this->config->disableIf(false);
        $this->assertTrue($this->config->isEnabled());
    }

    public function testEnable()
    {
        $this->assertTrue($this->config->isEnabled());

        $this->config->disable();
        $this->config->enable();

        $this->assertTrue($this->config->isEnabled());
    }

    public function testEnableIf()
    {
        $this->config->enableIf(true);
        $this->assertTrue($this->config->isEnabled());

        $this->config->enableIf(false);
        $this->assertFalse($this->config->isEnabled());
    }

    /**
     * @dataProvider getValidNames
     */
    public function testAddAlias($alias)
    {
        // valid name, but invalid alias
        if (null === $alias) {
            $this->setExpectedException('InvalidArgumentException');
        }

        $this->config->addAlias($alias);

        $this->assertSame(array($alias), $this->config->getAliases());
    }

    public function testAddAliasPreservesExistingAliases()
    {
        $this->config->addAlias('alias1');
        $this->config->addAlias('alias2');

        $this->assertSame(array('alias1', 'alias2'), $this->config->getAliases());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasFailsIfNull()
    {
        $this->config->addAlias(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasFailsIfEmpty()
    {
        $this->config->addAlias('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasFailsIfNoString()
    {
        $this->config->addAlias(1234);
    }

    /**
     * @dataProvider getInvalidNames
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasFailsIfInvalidName($name)
    {
        $this->config->addAlias($name);
    }

    public function testAddAliases()
    {
        $this->config->addAlias('alias1');
        $this->config->addAliases(array('alias2', 'alias3'));

        $this->assertSame(array('alias1', 'alias2', 'alias3'), $this->config->getAliases());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasesFailsIfNull()
    {
        $this->config->addAliases(array(null));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasesFailsIfEmpty()
    {
        $this->config->addAliases(array(''));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasesFailsIfNoString()
    {
        $this->config->addAliases(array(1234));
    }

    public function testSetAliases()
    {
        $this->config->addAlias('alias1');
        $this->config->setAliases(array('alias2', 'alias3'));

        $this->assertSame(array('alias2', 'alias3'), $this->config->getAliases());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetAliasesFailsIfNull()
    {
        $this->config->setAliases(array(null));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetAliasesFailsIfEmpty()
    {
        $this->config->setAliases(array(''));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetAliasesFailsIfNoString()
    {
        $this->config->setAliases(array(1234));
    }

    public function testSetProcessTitle()
    {
        $this->config->setProcessTitle('title');

        $this->assertSame('title', $this->config->getProcessTitle());
    }

    public function testSetProcessTitleNull()
    {
        $this->config->setProcessTitle(null);

        $this->assertNull($this->config->getProcessTitle());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetProcessTitleFailsIfEmpty()
    {
        $this->config->setProcessTitle('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetProcessTitleFailsIfNotString()
    {
        $this->config->setProcessTitle(1234);
    }

    public function testSetCallback()
    {
        $this->config->setCallback($callback = function () {});

        $this->assertSame($callback, $this->config->getCallback());
    }

    public function testSetCallbackNull()
    {
        $this->config->setCallback(null);

        $this->assertNull($this->config->getCallback());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetCallbackFailsIfEmpty()
    {
        $this->config->setCallback('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetCallbackFailsIfNotCallable()
    {
        $this->config->setCallback(new stdClass());
    }

    public function testAddArgument()
    {
        $this->config->addArgument('argument1', InputArgument::REQUIRED, 'Description 1');
        $this->config->addArgument('argument2', InputArgument::OPTIONAL, 'Description 2', 'Default');

        $this->assertEquals(array(
            'argument1' => new InputArgument('argument1', InputArgument::REQUIRED, 'Description 1'),
            'argument2' => new InputArgument('argument2', InputArgument::OPTIONAL, 'Description 2', 'Default'),
        ), $this->config->getArguments());
    }

    public function testAddOption()
    {
        $this->config->addOption('option1', 'o', InputOption::VALUE_REQUIRED, 'Description 1');
        $this->config->addOption('option2', 'p', InputOption::VALUE_OPTIONAL, 'Description 2', 'Default');

        $this->assertEquals(array(
            'option1' => new InputOption('option1', 'o', InputOption::VALUE_REQUIRED, 'Description 1'),
            'option2' => new InputOption('option2', 'p', InputOption::VALUE_OPTIONAL, 'Description 2', 'Default'),
        ), $this->config->getOptions());
    }

    public function testAddSubCommand()
    {
        $this->config->addSubCommandConfig($config1 = new SubCommandConfig('add'));
        $this->config->addSubCommandConfig($config2 = new SubCommandConfig('remove'));

        $this->assertSame(array(
            'add' => $config1,
            'remove' => $config2,
        ), $this->config->getSubCommandConfigs());
    }

    public function testBeginSubCommand()
    {
        $this->config
            ->beginSubCommand('add')->end()
            ->beginSubCommand('remove')->end()
        ;

        $this->assertEquals(array(
            'add' => new SubCommandConfig('add', $this->config),
            'remove' => new SubCommandConfig('remove', $this->config),
        ), $this->config->getSubCommandConfigs());
    }

    public function testAddOptionCommandConfig()
    {
        $this->config->addOptionCommandConfig($config1 = new OptionCommandConfig('add', 'a'));
        $this->config->addOptionCommandConfig($config2 = new OptionCommandConfig('delete', 'd'));

        $this->assertSame(array(
            'add' => $config1,
            'delete' => $config2,
        ), $this->config->getOptionCommandConfigs());
    }

    public function testBeginOptionCommand()
    {
        $this->config
            ->beginOptionCommand('add', 'a')->end()
            ->beginOptionCommand('delete', 'd')->end()
        ;

        $this->assertEquals(array(
            'add' => new OptionCommandConfig('add', 'a', $this->config),
            'delete' => new OptionCommandConfig('delete', 'd', $this->config),
        ), $this->config->getOptionCommandConfigs());
    }

    public function testGetHandler()
    {
        $command = new Command($this->config);

        $this->assertEquals(new NullHandler(), $this->config->getHandler($command));
    }

    public function testGetHandlerWithRunnable()
    {
        $config = new TestRunnableConfig('command');
        $command = new Command($config);

        $handler = $config->getHandler($command);
        $handler->initialize($command, new BufferedOutput(), new BufferedOutput());

        $this->assertInstanceOf('Webmozart\Console\Handler\RunnableHandler', $handler);
        $this->assertSame('foo', $handler->handle(new StringInput('test')));
    }

    public function testGetHandlerWithCallback()
    {
        $this->config->setCallback($callback = function () { return 'foo'; });
        $command = new Command($this->config);

        $handler = $this->config->getHandler($command);
        $handler->initialize($command, new BufferedOutput(), new BufferedOutput());

        $this->assertInstanceOf('Webmozart\Console\Handler\CallableHandler', $handler);
        $this->assertSame('foo', $handler->handle(new StringInput('test')));
    }

    public function testDefaultToSubCommand()
    {
        $this->assertNull($this->config->getDefaultSubCommand());

        $this->config
            ->addSubCommandConfig(new SubCommandConfig('sub'))
            ->defaultToSubCommand('sub')
        ;

        $this->assertSame('sub', $this->config->getDefaultSubCommand());
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage sub
     */
    public function testDefaultToSubCommandFailsIfNotFound()
    {
        $this->config->defaultToSubCommand('sub');
    }

    public function testDefaultToOptionCommand()
    {
        $this->assertNull($this->config->getDefaultOptionCommand());

        $this->config
            ->addOptionCommandConfig(new OptionCommandConfig('option'))
            ->defaultToOptionCommand('option')
        ;

        $this->assertSame('option', $this->config->getDefaultOptionCommand());
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage option
     */
    public function testDefaultToOptionCommandFailsIfNotFound()
    {
        $this->config->defaultToOptionCommand('option');
    }

    public function testDefaultToSubCommandResetsDefaultOptionCommand()
    {
        $this->config
            ->addSubCommandConfig(new SubCommandConfig('sub'))
            ->addOptionCommandConfig(new OptionCommandConfig('option'))
            ->defaultToOptionCommand('option')
            ->defaultToSubCommand('sub')
        ;

        $this->assertSame('sub', $this->config->getDefaultSubCommand());
        $this->assertNull($this->config->getDefaultOptionCommand());
    }

    public function testDefaultToOptionCommandResetsDefaultSubCommand()
    {
        $this->config
            ->addSubCommandConfig(new SubCommandConfig('sub'))
            ->addOptionCommandConfig(new OptionCommandConfig('option'))
            ->defaultToSubCommand('sub')
            ->defaultToOptionCommand('option')
        ;

        $this->assertNull($this->config->getDefaultSubCommand());
        $this->assertSame('option', $this->config->getDefaultOptionCommand());
    }
}
