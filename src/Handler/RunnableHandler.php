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
use Webmozart\Console\Api\Runnable;
use Webmozart\Console\Assert\Assert;

/**
 * Delegates command handling to a {@link Runnable} object.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RunnableHandler implements CommandHandler
{
    /**
     * @var Runnable
     */
    private $runnable;

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
     * @param Runnable $runnable The object to run when handling a command.
     */
    public function __construct(Runnable $runnable)
    {
        $this->runnable = $runnable;
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
        $this->runnable->run($input, $this->output, $this->errorOutput);
    }
}
