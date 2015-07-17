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

/**
 * Parses tokens from a string passed to {@link StringArgs}.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TokenParser
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var int
     */
    private $cursor;

    /**
     * @var string|null
     */
    private $current;

    /**
     * @var string|null
     */
    private $next;

    /**
     * Parses the tokens from the given string.
     *
     * @param string $string The console arguments string.
     *
     * @return array The tokens.
     */
    public function parseTokens($string)
    {
        $this->string = $string;
        $this->cursor = 0;
        $this->current = isset($this->string[0]) ? $this->string[0] : null;
        $this->next = isset($this->string[1]) ? $this->string[1] : null;

        // Unify result of ctype_space() across systems
        $previousLocale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $tokens = $this->doParseTokens();
        setlocale(LC_CTYPE, $previousLocale);

        return $tokens;
    }

    /**
     * Advances the cursor to the next position.
     */
    private function next()
    {
        if (!$this->valid()) {
            return;
        }

        ++$this->cursor;
        $this->current = $this->next;
        $this->next = isset($this->string[$this->cursor + 1]) ? $this->string[$this->cursor + 1] : null;
    }

    /**
     * Returns whether the cursor position is valid.
     *
     * @return bool Returns `false` once the cursor moved beyond the end of the
     *              string and `true` otherwise.
     */
    private function valid()
    {
        return null !== $this->current;
    }

    /**
     * Parses an array of tokens.
     *
     * @return array The parsed tokens.
     */
    private function doParseTokens()
    {
        $tokens = array();

        while ($this->valid()) {
            while (ctype_space($this->current)) {
                // Skip spaces
                $this->next();
            }

            if ($this->valid()) {
                $tokens[] = $this->parseToken();
            }
        }

        return $tokens;
    }

    /**
     * Parses a single token.
     *
     * @return string The parsed token.
     */
    private function parseToken()
    {
        $token = '';

        while ($this->valid()) {
            if (ctype_space($this->current)) {
                // Skip space
                $this->next();

                break;
            }

            if ('\\' === $this->current) {
                $token .= $this->parseEscapeSequence();
            } elseif ("'" === $this->current || '"' === $this->current) {
                $token .= $this->parseQuotedString();
            } else {
                $token .= $this->current;
                $this->next();
            }
        }

        return $token;
    }

    /**
     * Parses a quoted string.
     *
     * The string is delimited by the current symbol.
     *
     * @return string The string without delimiting quotes..
     */
    private function parseQuotedString()
    {
        $string = '';
        $delimiter = $this->current;

        // Skip first delimiter
        $this->next();

        while ($this->valid()) {
            if ($delimiter === $this->current) {
                // Skip last delimiter
                $this->next();

                break;
            }

            if ('\\' === $this->current) {
                $string .= $this->parseEscapeSequence();
            } elseif ('"' === $this->current) {
                $string .= '"'.$this->parseQuotedString().'"';
            } elseif ("'" === $this->current) {
                $string .= "'".$this->parseQuotedString()."'";
            } else {
                $string .= $this->current;
                $this->next();
            }
        }

        return $string;
    }

    /**
     * Parses an escape sequence started by a backslash.
     *
     * @return string The parsed sequence.
     */
    private function parseEscapeSequence()
    {
        $sequence = "'" === $this->next || '"' === $this->next
            ? $this->next
            : '\\'.$this->next;

        $this->next();
        $this->next();

        return $sequence;
    }
}
