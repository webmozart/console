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
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandConfig;
use Webmozart\Console\Tests\Api\Command\Fixtures\TestNestedConfig;
use Webmozart\Console\Tests\Api\Command\Fixtures\TestNestedRunnableConfig;
use Webmozart\Console\Tests\Api\Command\Fixtures\TestRunnableConfig;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SubCommandConfigTest extends PHPUnit_Framework_TestCase
{
    public function testGetHandlerInheritsParentHandlerByDefault()
    {
        $parentConfig = new CommandConfig();
        $parentConfig->setCallback($callback = function () { return 'foo'; });

        $config = new TestNestedConfig('command', $parentConfig);

        $handler = $config->getHandler(new Command($config));

        $this->assertInstanceOf('Webmozart\Console\Handler\CallableHandler', $handler);
        $this->assertSame('foo', $handler->handle(new StringInput('test')));
    }

    public function testGetHandlerWithCallback()
    {
        $parentConfig = new CommandConfig();
        $parentConfig->setCallback($parentCallback = function () { return 'foo'; });

        $config = new TestNestedConfig('command', $parentConfig);
        $config->setCallback($callback = function () { return 'bar'; });
        $command = new Command($config);

        $handler = $config->getHandler($command);
        $handler->initialize($command, new BufferedOutput(), new BufferedOutput());

        $this->assertInstanceOf('Webmozart\Console\Handler\CallableHandler', $handler);
        $this->assertSame('bar', $handler->handle(new StringInput('test')));
    }

    public function testGetHandlerWithRunnable()
    {
        $parentConfig = new TestRunnableConfig();
        $config = new TestNestedRunnableConfig('command', $parentConfig);
        $command = new Command($config);

        $handler = $config->getHandler($command);
        $handler->initialize($command, new BufferedOutput(), new BufferedOutput());

        $this->assertInstanceOf('Webmozart\Console\Handler\RunnableHandler', $handler);
        $this->assertSame('bar', $handler->handle(new StringInput('test')));
    }
}
