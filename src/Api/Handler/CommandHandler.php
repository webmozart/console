<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Handler;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Api\Command\Command;

/**
 * Handles a console command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface CommandHandler
{
    /**
     * Initializes the command handler.
     *
     * @param Command         $command     The command to handle.
     * @param OutputInterface $output      The standard output.
     * @param OutputInterface $errorOutput The error output.
     */
    public function initialize(Command $command, OutputInterface $output, OutputInterface $errorOutput);

    /**
     * Handles a command.
     *
     * @param InputInterface $input The console input.
     *
     * @return int Returns 0 on success and a positive integer on error.
     */
    public function handle(InputInterface $input);
}
