<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Args;

use Webmozart\Console\Api\Args\RawArgs;

/**
 * Console arguments passed via PHP's "argv" variable.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgvArgs implements RawArgs
{
    /**
     * @var string[]
     */
    private $tokens;

    /**
     * Creates the console arguments.
     *
     * @param array $argv The contents of the "argv" variable or `null` to read
     *                    the global "argv" variable.
     */
    public function __construct(array $argv = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }

        // Remove the application name from the tokens
        array_shift($argv);

        $this->tokens = $argv;
    }

    /**
     * {@inheritdoc}
     */
    public function hasToken($token)
    {
        return in_array($token, $this->tokens);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return implode(' ', $this->tokens);
    }
}
