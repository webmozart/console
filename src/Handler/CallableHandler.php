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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Handler\CommandHandler;
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
     * @var OutputInterface
     */
    private $output;

    /**
     * @var OutputInterface
     */
    private $errorOutput;

    /**
     * Creates the command handler.
     *
     * The passed callable receives three arguments:
     *
     *  * {@link InputInterface} `$input`: The console input.
     *  * {@link OutputInterface} `$output`: The standard output.
     *  * {@link OutputInterface} `$errorOutput`: The error output.
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
    public function initialize(Command $command, OutputInterface $output, OutputInterface $errorOutput)
    {
        $this->output = $output;
        $this->errorOutput = $errorOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(InputInterface $input)
    {
        call_user_func($this->callable, $input, $this->output, $this->errorOutput);
    }
}
