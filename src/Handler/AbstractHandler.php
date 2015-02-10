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
use Webmozart\Console\Api\Output\Output;

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
     * @var Output
     */
    protected $output;

    /**
     * @var Output
     */
    protected $errorOutput;

    /**
     * {@inheritdoc}
     */
    public function initialize(Command $command, Output $output, Output $errorOutput)
    {
        $this->command = $command;
        $this->output = $output;
        $this->errorOutput = $errorOutput;
    }
}
