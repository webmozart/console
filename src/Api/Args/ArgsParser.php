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

use Webmozart\Console\Api\Args\Format\ArgsFormat;

/**
 * Parses raw console arguments and returns the parsed arguments.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ArgsParser
{
    /**
     * Parses the raw console arguments and returns the parsed arguments.
     *
     * @param RawArgs    $args    The raw console arguments.
     * @param ArgsFormat $format  The argument format.
     * @param bool       $lenient Whether the parser should ignore parse errors.
     *                            If `true`, the parser will not throw any
     *                            exceptions when parse errors occur.
     *
     * @return Args The parsed console arguments.
     *
     * @throws CannotParseArgsException If the arguments cannot be parsed.
     */
    public function parseArgs(RawArgs $args, ArgsFormat $format, $lenient = false);
}
