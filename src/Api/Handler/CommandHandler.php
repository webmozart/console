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
use Webmozart\Console\Api\IO\IO;

/**
 * Handles a console command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface CommandHandler
{
    /**
     * Handles a command.
     *
     * @param Command $command The command to handle.
     * @param Args    $args    The console arguments.
     * @param IO      $io      The I/O.
     *
     * @return int Returns 0 on success and a positive integer on error.
     */
    public function handle(Command $command, Args $args, IO $io);
}
