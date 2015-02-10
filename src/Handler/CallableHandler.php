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

use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Assert\Assert;

/**
 * Delegates command handling to a callable.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CallableHandler extends AbstractHandler
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
     *  * {@link Input} `$input`: The console input.
     *  * {@link Output} `$output`: The standard output.
     *  * {@link Output} `$errorOutput`: The error output.
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
    public function handle(Input $input)
    {
        return call_user_func($this->callable, $input, $this->output, $this->errorOutput);
    }
}
