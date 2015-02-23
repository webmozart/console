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
use stdClass;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Handler\CallbackHandler;
use Webmozart\Console\Handler\DelegatingHandler;
use Webmozart\Console\IO\BufferedIO;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DelegatingHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var Args
     */
    private $args;

    /**
     * @var BufferedIO
     */
    private $io;

    protected function setUp()
    {
        $this->command = new Command(new CommandConfig());
        $this->args = new Args(new ArgsFormat());
        $this->io = new BufferedIO();
    }

    public function testGetHandlerNames()
    {
        $handler = new DelegatingHandler();

        $this->assertSame(array(), $handler->getRegisteredNames());

        $handler->register('handler1', new CallbackHandler(function () {}));
        $handler->register('handler2', new CallbackHandler(function () {}));

        $this->assertSame(array('handler1', 'handler2'), $handler->getRegisteredNames());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterFailsIfNameNull()
    {
        $handler = new DelegatingHandler();
        $handler->register(null, new CallbackHandler(function () {}));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterFailsIfNameEmpty()
    {
        $handler = new DelegatingHandler();
        $handler->register('', new CallbackHandler(function () {}));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterFailsIfNameNoString()
    {
        $handler = new DelegatingHandler();
        $handler->register(1234, new CallbackHandler(function () {}));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterFailsIfInvalidHandler()
    {
        $handler = new DelegatingHandler();
        $handler->register('handler', 1234);
    }

    public function testUnregister()
    {
        $handler = new DelegatingHandler();

        $handler->register('handler1', new CallbackHandler(function () {}));
        $handler->register('handler2', new CallbackHandler(function () {}));
        $handler->register('handler3', new CallbackHandler(function () {}));

        $handler->unregister('handler2');

        $this->assertSame(array('handler1', 'handler3'), $handler->getRegisteredNames());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage foobar
     */
    public function testSelectHandlerFailsIfUnknownHandler()
    {
        $handler = new DelegatingHandler();
        $handler->selectHandler('foobar');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSelectHandlerFailsIfHandlerNull()
    {
        $handler = new DelegatingHandler();
        $handler->selectHandler(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSelectHandlerFailsIfHandlerEmpty()
    {
        $handler = new DelegatingHandler();
        $handler->selectHandler('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSelectHandlerFailsIfHandlerNoStringNorCallable()
    {
        $handler = new DelegatingHandler();
        $handler->selectHandler(1234);
    }

    public function testDelegateToFirstHandlerByDefault()
    {
        $handler = new DelegatingHandler();
        $delegate = $this->getMock('stdClass', array('handle'));

        $delegate->expects($this->once())
            ->method('handle')
            ->with($this->command, $this->args, $this->io)
            ->willReturn(123);

        $handler->register('handler1', $delegate);
        $handler->register('handler2', new CallbackHandler(function () {}));

        $handler->handle($this->command, $this->args, $this->io);
    }

    public function testDelegateToSelectedHandler()
    {
        $handler = new DelegatingHandler();
        $delegate = $this->getMock('stdClass', array('handle'));

        $delegate->expects($this->once())
            ->method('handle')
            ->with($this->command, $this->args, $this->io)
            ->willReturn(123);

        $handler->register('handler1', new CallbackHandler(function () {}));
        $handler->register('handler2', $delegate);
        $handler->register('handler3', new CallbackHandler(function () {}));
        $handler->selectHandler('handler2');

        $handler->handle($this->command, $this->args, $this->io);
    }

    public function testDelegateToFirstHandlerIfSelectedHandlerUnregistered()
    {
        $handler = new DelegatingHandler();
        $delegate = $this->getMock('stdClass', array('handle'));

        $delegate->expects($this->once())
            ->method('handle')
            ->with($this->command, $this->args, $this->io)
            ->willReturn(123);

        $handler->register('handler1', $delegate);
        $handler->register('handler2', new CallbackHandler(function () {}));
        $handler->register('handler3', new CallbackHandler(function () {}));
        $handler->selectHandler('handler2');
        $handler->unregister('handler2');

        $handler->handle($this->command, $this->args, $this->io);
    }

    public function testDelegateToHandlerCreatedByFactoryCallback()
    {
        $command = $this->command;
        $args = $this->args;
        $io = $this->io;

        $handler = new DelegatingHandler();
        $delegate = $this->getMock('stdClass', array('handle'));

        $delegate->expects($this->once())
            ->method('handle')
            ->with($this->command, $this->args, $this->io)
            ->willReturn(123);

        $handler->register('handler1', new CallbackHandler(function () {}));
        $handler->register('handler2', function ($passedCommand, $passedArgs, $passedIO) use ($delegate, $command, $args, $io) {
            PHPUnit_Framework_Assert::assertSame($command, $passedCommand);
            PHPUnit_Framework_Assert::assertSame($args, $passedArgs);
            PHPUnit_Framework_Assert::assertSame($io, $passedIO);

            return $delegate;
        });
        $handler->register('handler3', new CallbackHandler(function () {}));
        $handler->selectHandler('handler2');

        $handler->handle($this->command, $this->args, $this->io);
    }

    public function testDelegateToHandlerSelectedByCallback()
    {
        $command = $this->command;
        $args = $this->args;
        $io = $this->io;

        $handler = new DelegatingHandler();
        $delegate = $this->getMock('stdClass', array('handle'));

        $delegate->expects($this->once())
            ->method('handle')
            ->with($this->command, $this->args, $this->io)
            ->willReturn(123);

        $handler->register('handler1', new CallbackHandler(function () {}));
        $handler->register('handler2', $delegate);
        $handler->register('handler3', new CallbackHandler(function () {}));

        $handler->selectHandler(function ($passedCommand, $passedArgs, $passedIO) use ($command, $args, $io) {
            PHPUnit_Framework_Assert::assertSame($command, $passedCommand);
            PHPUnit_Framework_Assert::assertSame($args, $passedArgs);
            PHPUnit_Framework_Assert::assertSame($io, $passedIO);

            return 'handler2';
        });

        $handler->handle($this->command, $this->args, $this->io);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage No handler was selected.
     */
    public function testFailIfNoHandlerSelected()
    {
        $handler = new DelegatingHandler();

        $handler->handle($this->command, $this->args, $this->io);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage foobar
     */
    public function testFailIfSelectedHandlerNotFound()
    {
        $handler = new DelegatingHandler();
        $handler->selectHandler(function () { return 'foobar'; });

        $handler->handle($this->command, $this->args, $this->io);
    }
}
