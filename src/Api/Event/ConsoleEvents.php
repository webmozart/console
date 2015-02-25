<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Event;

/**
 * Contains all the events supported by this package.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class ConsoleEvents
{
    /**
     * Executed with a {@link PreResolveEvent} before the command is resolved
     * for the given console arguments. Add a listener to resolve to a custom
     * command.
     */
    const PRE_RESOLVE = 'pre-resolve';

    /**
     * May not be instantiated.
     */
    private function __construct()
    {
    }
}
