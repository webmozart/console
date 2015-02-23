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
use stdClass;
use Symfony\Component\Console\Helper\HelperSet;
use Webmozart\Console\Adapter\InputInterfaceAdapter;
use Webmozart\Console\Adapter\OutputInterfaceAdapter;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Config\Config;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Formatter\StyleSet;
use Webmozart\Console\Args\DefaultArgsParser;
use Webmozart\Console\Formatter\DefaultStyleSet;
use Webmozart\Console\Handler\CallbackHandler;
use Webmozart\Console\Handler\NullHandler;
use Webmozart\Console\Tests\Api\Config\Fixtures\ConcreteConfig;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    protected function setUp()
    {
        $this->config = new ConcreteConfig();
    }

    public function testConfigure()
    {
        $config = new ConcreteConfig();

        $this->assertTrue($config->configureCalled);
    }

    public function testAddArgument()
    {
        $this->config->addArgument('argument1', Argument::REQUIRED, 'Description 1');
        $this->config->addArgument('argument2', Argument::OPTIONAL, 'Description 2', 'Default');

        $this->assertEquals(array(
            'argument1' => new Argument('argument1', Argument::REQUIRED, 'Description 1'),
            'argument2' => new Argument('argument2', Argument::OPTIONAL, 'Description 2', 'Default'),
        ), $this->config->getArguments());
    }

    public function testAddOption()
    {
        $this->config->addOption('option1', 'o', Option::REQUIRED_VALUE, 'Description 1');
        $this->config->addOption('option2', 'p', Option::OPTIONAL_VALUE, 'Description 2', 'Default');

        $this->assertEquals(array(
            'option1' => new Option('option1', 'o', Option::REQUIRED_VALUE, 'Description 1'),
            'option2' => new Option('option2', 'p', Option::OPTIONAL_VALUE, 'Description 2', 'Default'),
        ), $this->config->getOptions());
    }

    public function testSetHelperSet()
    {
        $helperSet = new HelperSet();

        $this->config->setHelperSet($helperSet);

        $this->assertSame($helperSet, $this->config->getHelperSet());
    }

    public function testDefaultHelperSet()
    {
        $helperSet = new HelperSet();

        $this->assertEquals($helperSet, $this->config->getHelperSet());
    }

    public function testSetStyleSet()
    {
        $styleSet = new StyleSet();

        $this->config->setStyleSet($styleSet);

        $this->assertSame($styleSet, $this->config->getStyleSet());
    }

    public function testDefaultStyleSet()
    {
        $styleSet = new DefaultStyleSet();

        $this->assertEquals($styleSet, $this->config->getStyleSet());
    }

    public function testSetArgsParser()
    {
        $parser = new DefaultArgsParser();

        $this->config->setArgsParser($parser);

        $this->assertSame($parser, $this->config->getArgsParser());
    }

    public function testDefaultArgsParser()
    {
        $parser = new DefaultArgsParser();

        $this->assertEquals($parser, $this->config->getArgsParser());
    }

    public function testSetHandler()
    {
        $handler = new stdClass();

        $this->config->setHandler($handler);

        $this->assertSame($handler, $this->config->getHandler());
    }

    public function testSetHandlerToFactoryCallback()
    {
        $handler = new stdClass();

        $factory = function () use ($handler) {
            return $handler;
        };

        $this->config->setHandler($factory);

        $this->assertSame($handler, $this->config->getHandler());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHandlerFailsIfNeitherObjectNorCallable()
    {
        $this->config->setHandler(1234);
    }

    public function testDefaultHandler()
    {
        $this->assertEquals(new NullHandler(), $this->config->getHandler());
    }

    public function testSetHandlerMethod()
    {
        $this->config->setHandlerMethod('handleFoo');

        $this->assertSame('handleFoo', $this->config->getHandlerMethod());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHandlerMethodFailsIfNull()
    {
        $this->config->setHandlerMethod(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHandlerMethodFailsIfEmpty()
    {
        $this->config->setHandlerMethod('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHandlerMethodFailsIfNoString()
    {
        $this->config->setHandlerMethod(1234);
    }

    public function testDefaultHandlerMethod()
    {
        $this->assertSame('handle', $this->config->getHandlerMethod());
    }

    public function testAddDefaultCommand()
    {
        $this->config->addDefaultCommandName('command1');
        $this->config->addDefaultCommandName('command2');

        $this->assertSame(array('command1', 'command2'), $this->config->getDefaultCommandNames());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddDefaultCommandFailsIfNull()
    {
        $this->config->addDefaultCommandName(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddDefaultCommandFailsIfEmpty()
    {
        $this->config->addDefaultCommandName('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddDefaultCommandFailsIfNotString()
    {
        $this->config->addDefaultCommandName(1234);
    }

    public function testAddDefaultCommands()
    {
        $this->config->addDefaultCommandName('command1');
        $this->config->addDefaultCommandNames(array('command2', 'command3'));

        $this->assertSame(array('command1', 'command2', 'command3'), $this->config->getDefaultCommandNames());
    }

    public function testSetDefaultCommands()
    {
        $this->config->addDefaultCommandName('command1');
        $this->config->setDefaultCommandNames(array('command2', 'command3'));

        $this->assertSame(array('command2', 'command3'), $this->config->getDefaultCommandNames());
    }

    public function testIsDefaultCommand()
    {
        $this->assertFalse($this->config->isDefaultCommandName('command'));
        $this->assertFalse($this->config->isDefaultCommandName('foobar'));

        $this->config->addDefaultCommandName('command');

        $this->assertTrue($this->config->isDefaultCommandName('command'));
        $this->assertFalse($this->config->isDefaultCommandName('foobar'));
    }

    public function testHasDefaultCommands()
    {
        $this->assertFalse($this->config->hasDefaultCommandNames());

        $this->config->addDefaultCommandName('command');

        $this->assertTrue($this->config->hasDefaultCommandNames());
    }
}
