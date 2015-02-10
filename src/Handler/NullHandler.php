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

use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Handler\CommandHandler;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;

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
    public function initialize(Command $command, Output $output, Output $errorOutput)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Input $input)
    {
    }
}
