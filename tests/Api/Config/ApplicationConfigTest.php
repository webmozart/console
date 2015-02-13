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
use Symfony\Component\EventDispatcher\EventDispatcher;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Output\Dimensions;
use Webmozart\Console\Resolver\DefaultResolver;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ApplicationConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ApplicationConfig
     */
    private $config;

    protected function setUp()
    {
        $this->config = new ApplicationConfig();
    }

    public function testCreate()
    {
        $config = new ApplicationConfig();

        $this->assertNull($config->getName());
        $this->assertNull($config->getDisplayName());
        $this->assertNull($config->getVersion());
        $this->assertNull($config->getDispatcher());
        $this->assertEquals(Dimensions::forCurrentWindow(), $config->getOutputDimensions());
        $this->assertSame(array(), $config->getCommandConfigs());
        $this->assertSame(array(), $config->getUnnamedCommandConfigs());
    }

    public function testCreateWithArguments()
    {
        $config = new ApplicationConfig('name', 'version');

        $this->assertSame('name', $config->getName());
        $this->assertSame('version', $config->getVersion());
    }

    public function testStaticCreate()
    {
        $config = ApplicationConfig::create();

        $this->assertNull($config->getName());
        $this->assertNull($config->getDisplayName());
        $this->assertNull($config->getVersion());
        $this->assertNull($config->getDispatcher());
        $this->assertEquals(Dimensions::forCurrentWindow(), $config->getOutputDimensions());
        $this->assertSame(array(), $config->getCommandConfigs());
        $this->assertSame(array(), $config->getUnnamedCommandConfigs());
    }

    public function testStaticCreateWithArguments()
    {
        $config = ApplicationConfig::create('name', 'version');

        $this->assertSame('name', $config->getName());
        $this->assertSame('version', $config->getVersion());
    }

    public function testSetName()
    {
        $this->config->setName('the-name');

        $this->assertSame('the-name', $this->config->getName());
    }

    public function testSetNameNull()
    {
        $this->config->setName('the-name');
        $this->config->setName(null);

        $this->assertNull($this->config->getName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetNameFailsIfEmpty()
    {
        $this->config->setName('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetNameFailsIfSpaces()
    {
        $this->config->setName('the name');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetNameFailsIfNoString()
    {
        $this->config->setName(1234);
    }

    public function testSetDisplayName()
    {
        $this->config->setDisplayName('The Name');

        $this->assertSame('The Name', $this->config->getDisplayName());
    }

    public function testSetDisplayNameNull()
    {
        $this->config->setDisplayName('The Name');
        $this->config->setDisplayName(null);

        $this->assertNull($this->config->getDisplayName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDisplayNameFailsIfEmpty()
    {
        $this->config->setDisplayName('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDisplayNameFailsIfNoString()
    {
        $this->config->setDisplayName(1234);
    }

    public function testGetDisplayNameReturnsHumanizedNameByDefault()
    {
        $this->config->setName('the-name');

        $this->assertSame('The Name', $this->config->getDisplayName());
    }

    public function testSetVersion()
    {
        $this->config->setVersion('version');

        $this->assertSame('version', $this->config->getVersion());
    }

    public function testSetVersionNull()
    {
        $this->config->setVersion('version');
        $this->config->setVersion(null);

        $this->assertNull($this->config->getVersion());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetVersionFailsIfEmpty()
    {
        $this->config->setVersion('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetVersionFailsIfNoString()
    {
        $this->config->setVersion(1234);
    }

    public function testSetDispatcher()
    {
        $dispatcher = new EventDispatcher();

        $this->config->setDispatcher($dispatcher);

        $this->assertSame($dispatcher, $this->config->getDispatcher());
    }

    public function testSetCatchExceptions()
    {
        $this->config->setCatchExceptions(true);
        $this->assertTrue($this->config->isExceptionCaught());

        $this->config->setCatchExceptions(false);
        $this->assertFalse($this->config->isExceptionCaught());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetCatchExceptionsFailsIfNull()
    {
        $this->config->setCatchExceptions(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetCatchExceptionsFailsIfNoBoolean()
    {
        $this->config->setCatchExceptions(1234);
    }

    public function testSetTerminateAfterRun()
    {
        $this->config->setTerminateAfterRun(true);
        $this->assertTrue($this->config->isTerminatedAfterRun());

        $this->config->setTerminateAfterRun(false);
        $this->assertFalse($this->config->isTerminatedAfterRun());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetTerminateAfterRunFailsIfNull()
    {
        $this->config->setTerminateAfterRun(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetTerminateAfterRunFailsIfNoBoolean()
    {
        $this->config->setTerminateAfterRun(1234);
    }

    public function testSetOutputDimensions()
    {
        $dimensions = new Dimensions(12, 34);

        $this->config->setOutputDimensions($dimensions);

        $this->assertSame($dimensions, $this->config->getOutputDimensions());
    }

    public function testSetCommandResolver()
    {
        $resolver = new DefaultResolver();

        $this->config->setCommandResolver($resolver);

        $this->assertSame($resolver, $this->config->getCommandResolver());
    }

    public function testDefaultCommandResolver()
    {
        $resolver = new DefaultResolver();

        $this->assertEquals($resolver, $this->config->getCommandResolver());
    }

    public function testBeginCommand()
    {
        $this->config
            ->beginCommand('command1')->end()
            ->beginCommand('command2')->end()
        ;

        $this->assertEquals(array(
            new CommandConfig('command1', $this->config),
            new CommandConfig('command2', $this->config),
        ), $this->config->getCommandConfigs());
    }

    public function testAddCommandConfig()
    {
        $this->config->addCommandConfig($config1 = new CommandConfig('command1'));
        $this->config->addCommandConfig($config2 = new CommandConfig('command2'));

        $this->assertSame(array($config1, $config2), $this->config->getCommandConfigs());
    }

    public function testAddCommandConfigs()
    {
        $this->config->addCommandConfig($config1 = new CommandConfig('command1'));
        $this->config->addCommandConfigs(array(
            $config2 = new CommandConfig('command2'),
            $config3 = new CommandConfig('command3'),
        ));

        $this->assertSame(array($config1, $config2, $config3), $this->config->getCommandConfigs());
    }

    public function testSetCommandConfigs()
    {
        $this->config->addCommandConfig($config1 = new CommandConfig('command1'));
        $this->config->setCommandConfigs(array(
            $config2 = new CommandConfig('command2'),
            $config3 = new CommandConfig('command3'),
        ));

        $this->assertSame(array($config2, $config3), $this->config->getCommandConfigs());
    }

    public function testGetCommandConfig()
    {
        $this->config->addCommandConfig($config = new CommandConfig());

        $config->setName('command');

        $this->assertSame($config, $this->config->getCommandConfig('command'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\NoSuchCommandException
     */
    public function testGetCommandConfigFailsIfCommandNotFound()
    {
        $this->config->getCommandConfig('command');
    }

    public function testHasCommandConfig()
    {
        $this->config->addCommandConfig($config = new CommandConfig());

        $this->assertFalse($this->config->hasCommandConfig('command'));
        $this->assertFalse($this->config->hasCommandConfig('foobar'));

        $config->setName('command');

        $this->assertTrue($this->config->hasCommandConfig('command'));
        $this->assertFalse($this->config->hasCommandConfig('foobar'));
    }

    public function testHasCommandConfigs()
    {
        $this->assertFalse($this->config->hasCommandConfigs());

        $this->config->addCommandConfig($config = new CommandConfig());

        $this->assertTrue($this->config->hasCommandConfigs());
    }

    public function testBeginUnnamedCommand()
    {
        $this->config
            ->beginUnnamedCommand()->setProcessTitle('title1')->end()
            ->beginUnnamedCommand()->setProcessTitle('title2')->end()
        ;

        $this->assertEquals(array(
            CommandConfig::create(null, $this->config)->setProcessTitle('title1'),
            CommandConfig::create(null, $this->config)->setProcessTitle('title2'),
        ), $this->config->getUnnamedCommandConfigs());
    }

    public function testAddUnnamedCommandConfig()
    {
        $this->config->addUnnamedCommandConfig($config1 = new CommandConfig());
        $this->config->addUnnamedCommandConfig($config2 = new CommandConfig());

        $this->assertSame(array($config1, $config2), $this->config->getUnnamedCommandConfigs());
    }

    public function testAddUnnamedCommandConfigs()
    {
        $this->config->addUnnamedCommandConfig($config1 = new CommandConfig());
        $this->config->addUnnamedCommandConfigs(array(
            $config2 = new CommandConfig(),
            $config3 = new CommandConfig(),
        ));

        $this->assertSame(array($config1, $config2, $config3), $this->config->getUnnamedCommandConfigs());
    }

    public function testSetUnnamedCommandConfigs()
    {
        $this->config->addUnnamedCommandConfig($config1 = new CommandConfig());
        $this->config->setUnnamedCommandConfigs(array(
            $config2 = new CommandConfig(),
            $config3 = new CommandConfig(),
        ));

        $this->assertSame(array($config2, $config3), $this->config->getUnnamedCommandConfigs());
    }

    public function testHasUnnamedCommandConfigs()
    {
        $this->assertFalse($this->config->hasUnnamedCommandConfigs());

        $this->config->addUnnamedCommandConfig($config = new CommandConfig());

        $this->assertTrue($this->config->hasUnnamedCommandConfigs());
    }
}
