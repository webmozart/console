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

use PHPUnit_Framework_Assert;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Console\Adapter\OutputInterfaceAdapter;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Handler\CallableHandler;
use Webmozart\Console\Input\StringInput;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CallableHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testHandleCommand()
    {
        $args = new Args(new ArgsFormat());
        $input = new StringInput("line1\nline2");
        $output = new OutputInterfaceAdapter($buffer1 = new BufferedOutput());
        $errorOutput = new OutputInterfaceAdapter($buffer2 = new BufferedOutput());
        $command = new Command(new CommandConfig('command'));

        $handler = new CallableHandler(
            function (Args $passedArgs, Input $input, Output $output, Output $errorOutput) use ($args) {
                PHPUnit_Framework_Assert::assertSame($args, $passedArgs);

                $output->write($input->readLine());
                $errorOutput->write($input->readLine());

                return 123;
            }
        );

        $handler->initialize($command, $output, $errorOutput);

        $this->assertSame(123, $handler->handle($args, $input));
        $this->assertSame("line1\n", $buffer1->fetch());
        $this->assertSame("line2", $buffer2->fetch());
    }
}
