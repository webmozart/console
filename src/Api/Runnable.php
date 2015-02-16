<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api;

use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;

/**
 * Executes a console command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Runnable
{
    /**
     * Executes the command for the given arguments.
     *
     * @param RawArgs $args        The console arguments.
     * @param Input   $input       The standard input.
     * @param Output  $output      The standard output.
     * @param Output  $errorOutput The error output.
     *
     * @return int Returns 0 on success and any other integer on error.
     */
    public function run(RawArgs $args, Input $input, Output $output, Output $errorOutput);
}
