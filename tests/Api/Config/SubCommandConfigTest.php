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
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\SubCommandConfig;
use Webmozart\Console\Args\DefaultArgsParser;
use Webmozart\Console\Handler\NullHandler;
use Webmozart\Console\Formatter\DefaultStyleSet;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SubCommandConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SubCommandConfig
     */
    private $config;

    /**
     * @var CommandConfig
     */
    private $parentConfig;

    /**
     * @var ApplicationConfig
     */
    private $applicationConfig;

    protected function setUp()
    {
        $this->applicationConfig = new ApplicationConfig();
        $this->parentConfig = new CommandConfig('command', $this->applicationConfig);
        $this->config = new SubCommandConfig('sub-command', $this->parentConfig, $this->applicationConfig);
    }

    public function testCreate()
    {
        $config = new SubCommandConfig();

        $this->assertNull($config->getParentConfig());
        $this->assertNull($config->getApplicationConfig());
        $this->assertNull($config->getName());
    }

    public function testCreateWithArguments()
    {
        $applicationConfig = new ApplicationConfig();
        $parentConfig = new CommandConfig('command', $applicationConfig);
        $config = new SubCommandConfig('sub-command', $parentConfig, $applicationConfig);

        $this->assertSame($parentConfig, $config->getParentConfig());
        $this->assertSame($applicationConfig, $config->getApplicationConfig());
        $this->assertSame('sub-command', $config->getName());
    }

    public function testGetHelperSetReturnsParentHelperSetByDefault()
    {
        $helperSet = new HelperSet();

        $this->parentConfig->setHelperSet($helperSet);

        $this->assertSame($helperSet, $this->config->getHelperSet());
    }

    public function testGetStyleSetReturnsParentStyleSetByDefault()
    {
        $styleSet = new DefaultStyleSet();

        $this->parentConfig->setStyleSet($styleSet);

        $this->assertSame($styleSet, $this->config->getStyleSet());
    }

    public function testGetHandlerReturnsParentHandlerByDefault()
    {
        $handler = new NullHandler();
        $command = new Command(new CommandConfig('command'));

        $this->parentConfig->setHandler($handler);

        $this->assertSame($handler, $this->config->getHandler($command));
    }

    public function testGetArgsParserReturnsParentArgsParserByDefault()
    {
        $parser = new DefaultArgsParser();

        $this->parentConfig->setArgsParser($parser);

        $this->assertSame($parser, $this->config->getArgsParser());
    }
}
