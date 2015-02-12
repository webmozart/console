<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Args;

/**
 * The unparsed console arguments.
 *
 * Implementations of this class represent the arguments that a user passes
 * when calling the console. For example:
 *
 * ```
 * $ console server add --port 80 localhost
 * ```
 *
 * In this case, the raw arguments contain the tokens:
 *
 *  * "server"
 *  * "add"
 *  * "--port"
 *  * "80"
 *  * "localhost"
 *
 * With an implementation of {@link ArgsParser} and a configured
 * {@link ArgsFormat}, the {@link RawArgs} instance can be converted into an
 * {@link Args} instance:
 *
 * ```php
 * $format = ArgsFormat::build()
 *     ->addCommandName(new CommandName('server'))
 *     ->addCommandName(new CommandName('add'))
 *     ->addOption(new Option('port', 'p', Option::VALUE_REQUIRED | Option::INTEGER))
 *     ->addArgument(new Argument('host', Argument::REQUIRED))
 *     ->getFormat();
 *
 * $args = $parser->parseArgs($rawArgs, $format);
 * ```
 *
 * The {@link Args} instance can be used to access the options and arguments of
 * a command in a convenient way.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see    Args, ArgsFormat, ArgsParser
 */
interface RawArgs
{
    /**
     * Returns the tokens of the console arguments.
     *
     * @return string[] The argument tokens.
     */
    public function getTokens();

    /**
     * Returns whether the console arguments contain a given token.
     *
     * @param string $token The token to look for.
     *
     * @return bool Returns `true` if the arguments contain the token and
     *              `false` otherwise.
     */
    public function hasToken($token);

    /**
     * Returns the console arguments as string.
     *
     * @return string The arguments as string.
     */
    public function toString();
}
