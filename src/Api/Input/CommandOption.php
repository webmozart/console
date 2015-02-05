<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Input;

/**
 * A command option in the input definition.
 *
 * The command names and command options of the input definition determine which
 * command is executed.
 *
 * In the example below, the input contains the command name "server" and the
 * command option "delete":
 *
 * ```
 * $ console server --delete localhost
 * $ console server -d localhost
 * ```
 *
 * The last part "localhost" is the argument to the "server --delete" command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandOption extends AbstractOption
{
}
