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
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class ConsoleEvents
{
    /**
     * Dispatched before console arguments are resolved to a command.
     *
     * @see PreResolveEvent
     */
    const PRE_RESOLVE = 'pre-resolve';

    /**
     * Dispatched before a command is handled.
     *
     * @see PreHandleEvent
     */
    const PRE_HANDLE = 'pre-handle';

    /**
     * Dispatched after building the configuration.
     *
     * @see ConfigEvent
     */
    const CONFIG = 'config';

    /**
     * May not be instantiated.
     */
    private function __construct()
    {
    }
}
