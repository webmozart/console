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

use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NoSuchCommandException;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\IO\InputStream;
use Webmozart\Console\Api\IO\OutputStream;
use Webmozart\Console\Api\Resolver\CannotResolveCommandException;
use Webmozart\Console\Api\Resolver\ResolvedCommand;

/**
 * A console application.
 *
 * @since  1.0
 *
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
     * Returns the commands that are not anonymous.
     *
     * @return CommandCollection The named commands.
     */
    public function getNamedCommands();

    /**
     * Returns whether the application has any commands that are not anonymous.
     *
     * @return bool Returns `true` if the application has named commands and
     *              `false` otherwise.
     *
     * @see getNamedCommands()
     */
    public function hasNamedCommands();

    /**
     * Returns the commands that should be executed if no explicit command is
     * passed.
     *
     * @return CommandCollection The default commands.
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
     * Returns the command to execute for the given console arguments.
     *
     * @param RawArgs $args The console arguments.
     *
     * @return ResolvedCommand The command to execute.
     *
     * @throws CannotResolveCommandException If the command cannot be resolved.
     */
    public function resolveCommand(RawArgs $args);

    /**
     * Executes the command.
     *
     * @param RawArgs      $args         The console arguments. If not given,
     *                                   the arguments passed to the PHP process
     *                                   are used.
     * @param InputStream  $inputStream  The standard input. If not given, the
     *                                   application reads from the standard
     *                                   input of the PHP process.
     * @param OutputStream $outputStream The standard output. If not given, the
     *                                   application prints to the standard
     *                                   output of the PHP process.
     * @param OutputStream $errorStream  The error output. If not given, the
     *                                   application prints to the error output
     *                                   of the PHP process.
     *
     * @return int The exit status.
     */
    public function run(RawArgs $args = null, InputStream $inputStream = null, OutputStream $outputStream = null, OutputStream $errorStream = null);
}
