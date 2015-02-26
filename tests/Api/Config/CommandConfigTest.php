<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Config;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\CommandName;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\Config\SubCommandConfig;
use Webmozart\Console\Args\DefaultArgsParser;
use Webmozart\Console\Formatter\DefaultStyleSet;
use Webmozart\Console\Handler\NullHandler;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ApplicationConfig
     */
    private $applicationConfig;

    /**
     * @var CommandConfig
     */
    private $config;

    protected function setUp()
    {
        $this->applicationConfig = new ApplicationConfig();
        $this->config = new CommandConfig('command', $this->applicationConfig);
    }

    public function testCreate()
    {
        $config = new CommandConfig();

        $this->assertNull($config->getName());
        $this->assertNull($config->getDescription());
        $this->assertNull($config->getHelp());
        $this->assertNull($config->getProcessTitle());
        $this->assertNull($config->getApplicationConfig());
        $this->assertSame(array(), $config->getAliases());
        $this->assertSame(array(), $config->getArguments());
        $this->assertSame(array(), $config->getOptions());
        $this->assertSame(array(), $config->getSubCommandConfigs());
    }

    public function testCreateWithArguments()
    {
        $config = new CommandConfig('command', $this->applicationConfig);

        $this->assertSame('command', $config->getName());
        $this->assertSame($this->applicationConfig, $config->getApplicationConfig());
    }

    public function testStaticCreate()
    {
        $config = CommandConfig::create();

        $this->assertNull($config->getName());
        $this->assertNull($config->getDescription());
        $this->assertNull($config->getHelp());
        $this->assertNull($config->getProcessTitle());
        $this->assertNull($config->getApplicationConfig());
        $this->assertSame(array(), $config->getAliases());
        $this->assertSame(array(), $config->getArguments());
        $this->assertSame(array(), $config->getOptions());
        $this->assertSame(array(), $config->getSubCommandConfigs());
    }

    public function testStaticCreateWithName()
    {
        $config = CommandConfig::create('command', $this->applicationConfig);

        $this->assertSame('command', $config->getName());
        $this->assertSame($this->applicationConfig, $config->getApplicationConfig());
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

    public function testBeginSubCommand()
    {
        $this->config
            ->beginSubCommand('command1')->end()
            ->beginSubCommand('command2')->end()
        ;

        $this->assertEquals(array(
            new SubCommandConfig('command1', $this->config, $this->applicationConfig),
            new SubCommandConfig('command2', $this->config, $this->applicationConfig),
        ), $this->config->getSubCommandConfigs());
    }

    public function testChangeSubCommand()
    {
        $this->config->addSubCommandConfig($config1 = new SubCommandConfig('command1'));

        $this->assertSame($config1, $this->config->editSubCommand('command1'));
    }

    public function testAddSubCommandConfig()
    {
        $this->config->addSubCommandConfig($config1 = new SubCommandConfig('command1'));
        $this->config->addSubCommandConfig($config2 = new SubCommandConfig('command2'));

        $this->assertSame(array($config1, $config2), $this->config->getSubCommandConfigs());

        $this->assertSame($this->applicationConfig, $config1->getApplicationConfig());
        $this->assertSame($this->applicationConfig, $config2->getApplicationConfig());
    }

    public function testAddSubCommandConfigs()
    {
        $this->config->addSubCommandConfig($config1 = new SubCommandConfig('command1'));
        $this->config->addSubCommandConfigs(array(
            $config2 = new SubCommandConfig('command2'),
            $config3 = new SubCommandConfig('command3'),
        ));

        $this->assertSame(array($config1, $config2, $config3), $this->config->getSubCommandConfigs());
    }

    public function testSetSubCommandConfigs()
    {
        $this->config->addSubCommandConfig($config1 = new SubCommandConfig('command1'));
        $this->config->setSubCommandConfigs(array(
            $config2 = new SubCommandConfig('command2'),
            $config3 = new SubCommandConfig('command3'),
        ));

        $this->assertSame(array($config2, $config3), $this->config->getSubCommandConfigs());
    }

    public function testGetSubCommandConfig()
    {
        $this->config->addSubCommandConfig($config = new SubCommandConfig());

        $config->setName('command');

        $this->assertSame($config, $this->config->getSubCommandConfig('command'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\NoSuchCommandException
     */
    public function testGetSubCommandConfigFailsIfCommandNotFound()
    {
        $this->config->getSubCommandConfig('command');
    }

    public function testHasSubCommandConfig()
    {
        $this->config->addSubCommandConfig($config = new SubCommandConfig());

        $this->assertFalse($this->config->hasSubCommandConfig('command'));
        $this->assertFalse($this->config->hasSubCommandConfig('foobar'));

        $config->setName('command');

        $this->assertTrue($this->config->hasSubCommandConfig('command'));
        $this->assertFalse($this->config->hasSubCommandConfig('foobar'));
    }

    public function testHasSubCommandConfigs()
    {
        $this->assertFalse($this->config->hasSubCommandConfigs());

        $this->config->addSubCommandConfig($config = new SubCommandConfig());

        $this->assertTrue($this->config->hasSubCommandConfigs());
    }

    public function testBeginOptionCommand()
    {
        $this->config
            ->beginOptionCommand('command1', 'a')->end()
            ->beginOptionCommand('command2', 'b')->end()
        ;

        $this->assertEquals(array(
            new OptionCommandConfig('command1', 'a', $this->config, $this->applicationConfig),
            new OptionCommandConfig('command2', 'b', $this->config, $this->applicationConfig),
        ), $this->config->getSubCommandConfigs());
    }

    public function testEditOptionCommand()
    {
        $this->config->addSubCommandConfig($config1 = new OptionCommandConfig('command1', 'a'));

        $this->assertSame($config1, $this->config->editOptionCommand('command1'));
    }

    public function testGetHelperSetReturnsApplicationHelperSetByDefault()
    {
        $helperSet = new HelperSet();

        $this->applicationConfig->setHelperSet($helperSet);

        $this->assertSame($helperSet, $this->config->getHelperSet());
    }

    public function testGetStyleSetReturnsApplicationStyleSetByDefault()
    {
        $styleSet = new DefaultStyleSet();

        $this->applicationConfig->setStyleSet($styleSet);

        $this->assertSame($styleSet, $this->config->getStyleSet());
    }

    public function testGetHandlerReturnsApplicationHandlerByDefault()
    {
        $handler = new NullHandler();

        $this->applicationConfig->setHandler($handler);

        $this->assertSame($handler, $this->config->getHandler());
    }

    public function testGetHandlerMethodReturnsApplicationHandlerByDefault()
    {
        $this->applicationConfig->setHandlerMethod('method');

        $this->assertSame('method', $this->config->getHandlerMethod());
    }

    public function testGetArgsParserReturnsApplicationArgsParserByDefault()
    {
        $parser = new DefaultArgsParser();

        $this->applicationConfig->setArgsParser($parser);

        $this->assertSame($parser, $this->config->getArgsParser());
    }

    public function testLenientArgsParsingDefaultsToApplicationValue()
    {
        $this->applicationConfig->enableLenientArgsParsing();

        $this->assertTrue($this->config->isLenientArgsParsingEnabled());

        $this->applicationConfig->disableLenientArgsParsing();

        $this->assertFalse($this->config->isLenientArgsParsingEnabled());
    }

    public function testMarkDefault()
    {
        $this->assertFalse($this->config->isDefault());
        $this->assertFalse($this->config->isAnonymous());

        $this->config->markDefault();

        $this->assertTrue($this->config->isDefault());
        $this->assertFalse($this->config->isAnonymous());
    }

    public function testMarkAnonymous()
    {
        $this->assertFalse($this->config->isDefault());
        $this->assertFalse($this->config->isAnonymous());

        $this->config->markAnonymous();

        $this->assertTrue($this->config->isDefault());
        $this->assertTrue($this->config->isAnonymous());
    }

    public function testMarkNoDefault()
    {
        $this->config->markAnonymous();

        $this->assertTrue($this->config->isDefault());
        $this->assertTrue($this->config->isAnonymous());

        $this->config->markNoDefault();

        $this->assertFalse($this->config->isDefault());
        $this->assertFalse($this->config->isAnonymous());
    }

    public function testBuildNamedArgsFormat()
    {
        $baseFormat = new ArgsFormat();
        $this->config->setName('command');
        $this->config->setAliases(array('alias1', 'alias2'));
        $this->config->addOption('option');
        $this->config->addArgument('argument');

        $expected = ArgsFormat::build($baseFormat)
            ->addCommandName(new CommandName('command', array('alias1', 'alias2')))
            ->addArgument(new Argument('argument'))
            ->addOption(new Option('option'))
            ->getFormat();

        $this->assertEquals($expected, $this->config->buildArgsFormat($baseFormat));
    }

    public function testBuildAnonymousArgsFormat()
    {
        $baseFormat = new ArgsFormat();
        $this->config->setName('command');
        $this->config->setAliases(array('alias1', 'alias2'));
        $this->config->addOption('option');
        $this->config->addArgument('argument');
        $this->config->markAnonymous();

        $expected = ArgsFormat::build($baseFormat)
            ->addArgument(new Argument('argument'))
            ->addOption(new Option('option'))
            ->getFormat();

        $this->assertEquals($expected, $this->config->buildArgsFormat($baseFormat));
    }
}
