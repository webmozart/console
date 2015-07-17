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
use Webmozart\Console\Handler\Help\HelpXmlHandler;
use Webmozart\Console\IO\BufferedIO;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpXmlHandlerTest extends PHPUnit_Framework_TestCase
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
     * @var HelpXmlHandler
     */
    private $handler;

    protected function setUp()
    {
        $config = DefaultApplicationConfig::create()
            ->setDisplayName('The Application')
            ->setVersion('1.2.3')
            ->beginCommand('the-command')->end()
        ;

        $this->application = new ConsoleApplication($config);
        $this->command = $this->application->getCommand('the-command');
        $this->helpCommand = $this->application->getCommand('help');
        $this->io = new BufferedIO();
        $this->handler = new HelpXmlHandler();
    }

    public function testRenderCommandXml()
    {
        $args = new Args($this->helpCommand->getArgsFormat());
        $args->setArgument('command', 'the-command');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $expected = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<command id="the-command" name="the-command">
EOF;

        $this->assertStringStartsWith($expected, $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    public function testRenderApplicationXml()
    {
        $args = new Args($this->helpCommand->getArgsFormat());

        $status = $this->handler->handle($args, $this->io, $this->command);

        $expected = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<symfony name="The Application" version="1.2.3">
EOF;

        $this->assertStringStartsWith($expected, $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }
}
