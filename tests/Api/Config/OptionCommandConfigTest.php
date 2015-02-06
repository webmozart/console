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
}
