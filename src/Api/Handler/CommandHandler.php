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

use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;

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
     * @param Command $command     The command to handle.
     * @param Output  $output      The standard output.
     * @param Output  $errorOutput The error output.
     */
    public function initialize(Command $command, Output $output, Output $errorOutput);

    /**
     * Handles a command.
     *
     * @param Args  $args  The console arguments.
     * @param Input $input The standard input.
     *
     * @return int Returns 0 on success and a positive integer on error.
     */
    public function handle(Args $args, Input $input);
}
