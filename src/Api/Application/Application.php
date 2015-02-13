<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Application;

use OutOfBoundsException;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NoSuchCommandException;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Output\Output;

/**
 * A console application.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Application
{
    /**
     * Returns the application configuration.
     *
     * @return ApplicationConfig The application configuration.
     */
    public function getConfig();

    /**
     * Returns the global arguments format of the application.
     *
     * @return ArgsFormat The global arguments format.
     */
    public function getGlobalArgsFormat();

    /**
     * Returns the command for a given name.
     *
     * @param string $name The name of the command.
     *
     * @return Command The command.
     *
     * @throws NoSuchCommandException If the command is not found.
     *
     * @see addCommand(), getCommands()
     */
    public function getCommand($name);

    /**
     * Returns all registered commands.
     *
     * @return CommandCollection The commands.
     *
     * @see addCommand(), getCommand()
     */
    public function getCommands();

    /**
     * Returns whether the application has a command with a given name.
     *
     * @param string $name The name of the command.
     *
     * @return bool Returns `true` if the command with the given name exists and
     *              `false` otherwise.
     *
     * @see hasCommands(), getCommand()
     */
    public function hasCommand($name);

    /**
     * Returns whether the application has any registered commands.
     *
     * @return bool Returns `true` if the application has any commands and
     *              `false` otherwise.
     *
     * @see hasCommand(), getCommands()
     */
    public function hasCommands();

    /**
     * Returns all unnamed commands of the application.
     *
     * @return Command[] The unnamed commands.
     */
    public function getUnnamedCommands();

    /**
     * Returns whether the application has any unnamed sub-commands.
     *
     * @return bool Returns `true` if the application has unnamed sub-commands
     *              and `false` otherwise.
     */
    public function hasUnnamedCommands();

    /**
     * Returns the commands that should be executed if no explicit command is
     * passed.
     *
     * @return Command[] The default commands.
     */
    public function getDefaultCommands();

    /**
     * Returns whether the application has any default commands.
     *
     * @return bool Returns `true` if the application has default commands and
     *              `false` otherwise.
     *
     * @see getDefaultCommands()
     */
    public function hasDefaultCommands();

    /**
     * Executes the command for a given input.
     *
     * @param Input  $input       The console input. If not given, the input
     *                            passed to the PHP process is used.
     * @param Output $output      The standard output. If not given, the
     *                            application prints to the standard output of
     *                            the PHP process.
     * @param Output $errorOutput The error output. If not given, the
     *                            application prints to the error output of the
     *                            PHP process.
     *
     * @return int The exit status.
     */
    public function run(Input $input = null, Output $output = null, Output $errorOutput = null);
}
