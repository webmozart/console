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

/**
 * A command handler that does nothing.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullHandler implements CommandHandler
{
    /**
     * {@inheritdoc}
     */
    public function initialize(Command $command, OutputInterface $output, OutputInterface $errorOutput)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(InputInterface $input)
    {
    }
}
