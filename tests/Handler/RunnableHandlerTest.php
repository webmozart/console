<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Handler;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandConfig;
use Webmozart\Console\Handler\RunnableHandler;
use Webmozart\Console\Tests\Handler\Fixtures\TestRunnable;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RunnableHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testHandleCommand()
    {
        $input = new StringInput('ls /');
        $output = new BufferedOutput();
        $errorOutput = new BufferedOutput();
        $command = new Command(new CommandConfig('command'));

        $handler = new RunnableHandler(new TestRunnable());

        $handler->initialize($command, $output, $errorOutput);

        $this->assertSame(123, $handler->handle($input));
        $this->assertSame("ls '/'", $output->fetch());
        $this->assertSame("ls '/'", $errorOutput->fetch());
    }
}
