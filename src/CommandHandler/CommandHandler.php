<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\CommandHandler;

use Webmozart\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param Command         $command The command that the handler belongs to.
     * @param OutputInterface $output  The console output.
     *
     * @return static
     */
    public function initialize(Command $command, OutputInterface $output);

    /**
     * Handles a console input.
     *
     * @param InputInterface $input The console input.
     *
     * @return int The result code.
     */
    public function handle(InputInterface $input);
}
