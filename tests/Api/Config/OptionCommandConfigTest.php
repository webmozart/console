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
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\CommandOption;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\OptionCommandConfig;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class OptionCommandConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var OptionCommandConfig
     */
    private $config;

    protected function setUp()
    {
        $this->config = new OptionCommandConfig();
    }

    public function testCreate()
    {
        $config = new OptionCommandConfig();

        $this->assertNull($config->getParentConfig());
        $this->assertNull($config->getApplicationConfig());
        $this->assertNull($config->getName());
        $this->assertNull($config->getLongName());
        $this->assertNull($config->getShortName());
    }

    public function testCreateWithArguments()
    {
        $applicationConfig = new ApplicationConfig();
        $parentConfig = new CommandConfig('command', $applicationConfig);
        $config = new OptionCommandConfig('delete', 'd', $parentConfig);

        $this->assertSame($parentConfig, $config->getParentConfig());
        $this->assertSame($applicationConfig, $config->getApplicationConfig());
        $this->assertSame('delete', $config->getName());
        $this->assertSame('delete', $config->getLongName());
        $this->assertSame('d', $config->getShortName());
    }

    /**
     * @dataProvider getInvalidNames
     * @expectedException \InvalidArgumentException
     */
    public function testSetNameFailsIfInvalid($name)
    {
        $this->config->setName($name);
    }

    public function getInvalidNames()
    {
        return array(
            array('a'),
            array('A'),
            array('1'),
            array(1234),
            array(true),
            array(''),
        );
    }

    public function testSetLongName()
    {
        $this->config->setLongName('delete');

        $this->assertSame('delete', $this->config->getLongName());
        $this->assertSame('delete', $this->config->getName());
    }

    /**
     * @dataProvider getValidShortNames
     */
    public function testSetShortName($name)
    {
        $this->config->setShortName($name);

        $this->assertSame($name, $this->config->getShortName());
    }

    public function getValidShortNames()
    {
        return array(
            array(null),
            array('a'),
            array('A'),
            array('z'),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getInvalidShortNames
     */
    public function testSetShortNameFailsIfInvalid($name)
    {
        $this->config->setShortName($name);
    }

    public function getInvalidShortNames()
    {
        return array(
            array(1234),
            array(true),
            array(''),
            array('ab'),
            array('&'),
            array('-'),
            array('_'),
            array('1'),
            array('9'),
        );
    }

    public function testSetShortNameOverwritesPreviousShortName()
    {
        $this->config->setShortName('c');
        $this->config->setShortName('d');

        $this->assertSame('d', $this->config->getShortName());
    }

    public function testShortNamePreferredByDefaultIfShortName()
    {
        $this->config->setShortName('d');

        $this->assertFalse($this->config->isLongNamePreferred());
        $this->assertTrue($this->config->isShortNamePreferred());
    }

    public function testLongNamePreferredByDefaultIfNoShortName()
    {
        $this->assertTrue($this->config->isLongNamePreferred());
        $this->assertFalse($this->config->isShortNamePreferred());
    }

    public function testSetPreferLongName()
    {
        $this->config->setPreferLongName();

        $this->assertTrue($this->config->isLongNamePreferred());
        $this->assertFalse($this->config->isShortNamePreferred());
    }

    public function testSetPreferShortName()
    {
        $this->config->setShortName('d');
        $this->config->setPreferShortName();

        $this->assertFalse($this->config->isLongNamePreferred());
        $this->assertTrue($this->config->isShortNamePreferred());
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetPreferShortNameFailsIfNoShortName()
    {
        $this->config->setPreferShortName();
    }

    public function testSetShortNameToNullSetsLongNameToBePreferred()
    {
        $this->config->setShortName('d');
        $this->config->setPreferShortName();
        $this->config->setShortName(null);

        $this->assertTrue($this->config->isLongNamePreferred());
        $this->assertFalse($this->config->isShortNamePreferred());
    }

    public function testBuildNamedArgsFormat()
    {
        $baseFormat = new ArgsFormat();
        $this->config->setName('command');
        $this->config->setShortName('c');
        $this->config->setAliases(array('alias1', 'alias2'));
        $this->config->addOption('option');
        $this->config->addArgument('argument');

        $expected = ArgsFormat::build($baseFormat)
            ->addCommandOption(new CommandOption('command', 'c', array('alias1', 'alias2')))
            ->addArgument(new Argument('argument'))
            ->addOption(new Option('option'))
            ->getFormat();

        $this->assertEquals($expected, $this->config->buildArgsFormat($baseFormat));
    }

    public function testBuildAnonymousArgsFormat()
    {
        $baseFormat = new ArgsFormat();
        $this->config->setName('command');
        $this->config->setShortName('c');
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
