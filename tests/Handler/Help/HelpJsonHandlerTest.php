<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Handler\Help;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Config\DefaultApplicationConfig;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Handler\Help\HelpJsonHandler;
use Webmozart\Console\IO\BufferedIO;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpJsonHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var Command
     */
    private $helpCommand;

    /**
     * @var BufferedIO
     */
    private $io;

    /**
     * @var HelpJsonHandler
     */
    private $handler;

    protected function setUp()
    {
        $config = DefaultApplicationConfig::create()
            ->beginCommand('the-command')->end()
        ;

        $this->application = new ConsoleApplication($config);
        $this->command = $this->application->getCommand('the-command');
        $this->helpCommand = $this->application->getCommand('help');
        $this->io = new BufferedIO();
        $this->handler = new HelpJsonHandler();
    }

    public function testRenderCommandJson()
    {
        $args = new Args($this->helpCommand->getArgsFormat());
        $args->setArgument('command', 'the-command');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertStringStartsWith('{"name":"the-command",', $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    public function testRenderApplicationJson()
    {
        $args = new Args($this->helpCommand->getArgsFormat());

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertStringStartsWith('{"commands":[{"name":"help",', $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }
}
