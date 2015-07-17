<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Util;

/**
 * Sets and resets the PHP process title.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ProcessTitle
{
    /**
     * @var bool
     */
    private static $supported;

    /**
     * @var string[]
     */
    private static $processTitles = array();

    /**
     * Returns whether process titles can be set.
     *
     * @return bool Returns `true` if process titles can be set and `false`
     *              otherwise.
     */
    public static function isSupported()
    {
        if (null === self::$supported) {
            self::$supported = function_exists('cli_set_process_title') || function_exists('setproctitle');
        }

        return self::$supported;
    }

    /**
     * Sets the title of the PHP process.
     *
     * @param string $processTitle The process title.
     */
    public static function setProcessTitle($processTitle)
    {
        self::$processTitles[] = $processTitle;

        self::changeProcessTitleTo($processTitle);
    }

    /**
     * Resets the title of the PHP process to the previous value.
     */
    public static function resetProcessTitle()
    {
        $processTitle = self::$processTitles ? array_pop(self::$processTitles) : null;

        self::changeProcessTitleTo($processTitle);
    }

    private static function changeProcessTitleTo($processTitle)
    {
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($processTitle);
        } elseif (function_exists('setproctitle')) {
            setproctitle($processTitle);
        }
    }

    private function __construct()
    {
    }
}
