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
use Webmozart\Console\Adapter\InputInterfaceAdapter;
use Webmozart\Console\Adapter\OutputInterfaceAdapter;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;
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
        $input = new InputInterfaceAdapter(new StringInput('ls /'));
        $output = new OutputInterfaceAdapter($buffer1 = new BufferedOutput());
        $errorOutput = new OutputInterfaceAdapter($buffer2 = new BufferedOutput());
        $command = new Command(new CommandConfig('command'));

        $callback = function (Input $input, Output $output, Output $errorOutput) {
            $output->write($input->toString());
            $errorOutput->write($input->toString());

            return 123;
        };

        $handler = new RunnableHandler(new TestRunnable($callback));

        $handler->initialize($command, $output, $errorOutput);

        $this->assertSame(123, $handler->handle($input));
        $this->assertSame("ls '/'", $buffer1->fetch());
        $this->assertSame("ls '/'", $buffer2->fetch());
    }
}
