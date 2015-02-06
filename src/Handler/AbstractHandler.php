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

use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Handler\CommandHandler;

/**
 * Base implementation for command handlers.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractHandler implements CommandHandler
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var OutputInterface
     */
    protected $errorOutput;

    /**
     * {@inheritdoc}
     */
    public function initialize(Command $command, OutputInterface $output, OutputInterface $errorOutput)
    {
        $this->command = $command;
        $this->output = $output;
        $this->errorOutput = $errorOutput;
    }
}
