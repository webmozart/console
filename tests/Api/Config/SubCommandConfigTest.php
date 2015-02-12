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
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Console\Adapter\InputInterfaceAdapter;
use Webmozart\Console\Adapter\OutputInterfaceAdapter;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\SubCommandConfig;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SubCommandConfigTest extends PHPUnit_Framework_TestCase
{
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
        $config = new SubCommandConfig('sub', $parentConfig, $applicationConfig);

        $this->assertSame($parentConfig, $config->getParentConfig());
        $this->assertSame($applicationConfig, $config->getApplicationConfig());
        $this->assertSame('sub', $config->getName());
    }

    public function testGetHandlerInheritsParentHandlerByDefault()
    {
        $parentConfig = new CommandConfig();
        $parentConfig->setCallback($callback = function () { return 'foo'; });

        $config = new SubCommandConfig('command', $parentConfig);
        $input = new InputInterfaceAdapter(new StringInput('test'));

        $handler = $config->getHandler(new Command($config));

        $this->assertInstanceOf('Webmozart\Console\Handler\CallableHandler', $handler);
        $this->assertSame('foo', $handler->handle($input));
    }

    public function testGetHandlerWithCallback()
    {
        $parentConfig = new CommandConfig();
        $parentConfig->setCallback($parentCallback = function () { return 'foo'; });

        $config = new SubCommandConfig('command', $parentConfig);
        $config->setCallback($callback = function () { return 'bar'; });
        $command = new Command($config);
        $input = new InputInterfaceAdapter(new StringInput('test'));
        $output = new OutputInterfaceAdapter(new BufferedOutput());

        $handler = $config->getHandler($command);
        $handler->initialize($command, $output, $output);

        $this->assertInstanceOf('Webmozart\Console\Handler\CallableHandler', $handler);
        $this->assertSame('bar', $handler->handle($input));
    }

    public function testSetHandler()
    {
        $handler = $this->getMock('Webmozart\Console\Api\Handler\CommandHandler');

        $parentConfig = new CommandConfig();
        $config = new SubCommandConfig('command', $parentConfig);
        $config->setHandler($handler);
        $command = new Command($config);

        $this->assertSame($handler, $config->getHandler($command));
    }

    public function testSetHandlerToFactoryCallback()
    {
        $handler = $this->getMock('Webmozart\Console\Api\Handler\CommandHandler');

        $factory = function (Command $command) use (&$passedCommand, $handler) {
            $passedCommand = $command;

            return $handler;
        };

        $parentConfig = new CommandConfig();
        $config = new SubCommandConfig('command', $parentConfig);
        $config->setHandler($factory);
        $command = new Command($config);

        $this->assertSame($handler, $config->getHandler($command));
        $this->assertSame($command, $passedCommand);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHandlerFailsIfNeitherCommandHandlerNorCallable()
    {
        $config = new SubCommandConfig('command');

        $config->setHandler(new stdClass());
    }

    public function testGetHelperSet()
    {
        $helperSet1 = new HelperSet();
        $helperSet2 = new HelperSet();

        $parentConfig = new CommandConfig();
        $config = new SubCommandConfig('command', $parentConfig);

        $parentConfig->setHelperSet($helperSet1);
        $config->setHelperSet($helperSet2);

        $this->assertSame($helperSet2, $config->getHelperSet());
    }

    public function testGetHelperSetReturnsParentHelperSetIfNotSet()
    {
        $helperSet = new HelperSet();

        $parentConfig = new CommandConfig();
        $config = new SubCommandConfig('command', $parentConfig);

        $parentConfig->setHelperSet($helperSet);

        $this->assertSame($helperSet, $config->getHelperSet());
    }

    public function testGetHelperSetReturnsApplicationHelperSetIfNotSet()
    {
        $helperSet = new HelperSet();

        $applicationConfig = new ApplicationConfig();
        $parentConfig = new CommandConfig(null, $applicationConfig);
        $config = new SubCommandConfig('command', $parentConfig);

        $applicationConfig->setHelperSet($helperSet);

        $this->assertSame($helperSet, $config->getHelperSet());
    }

    public function testGetHelperSetReturnsNullIfNotSetAndNoFallback()
    {
        $helperSet = new HelperSet();

        $parentConfig = new CommandConfig();
        $config = new SubCommandConfig('command', $parentConfig);

        $parentConfig->setHelperSet($helperSet);

        $this->assertNull($config->getHelperSet(false));
    }
}
