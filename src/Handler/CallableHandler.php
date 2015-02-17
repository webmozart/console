<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Handler;

use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Handler\CommandHandler;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Assert\Assert;

/**
 * Delegates command handling to a callable.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CallableHandler implements CommandHandler
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * Creates the command handler.
     *
     * The passed callable receives three arguments:
     *
     *  * {@link Command} `$command`: The executed command.
     *  * {@link Args} `$args`: The console arguments.
     *  * {@link IO} `$io`: The I/O.
     *
     * The callable should return 0 on success and a positive integer on error.
     *
     * @param callable $callable The callable to execute when handling a
     *                           command.
     */
    public function __construct($callable)
    {
        Assert::isCallable($callable);

        $this->callable = $callable;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Command $command, Args $args, IO $io)
    {
        return call_user_func($this->callable, $command, $args, $io);
    }
}
