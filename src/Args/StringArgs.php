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
 * Console arguments passed as a string.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StringArgs implements RawArgs
{
    /**
     * @var string[]
     */
    private $tokens;

    /**
     * Creates the console arguments.
     *
     * @param string $string The console arguments string.
     */
    public function __construct($string)
    {
        $parser = new StringArgsParser();

        $this->tokens = $parser->parse($string);
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
