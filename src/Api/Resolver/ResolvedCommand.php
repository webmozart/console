<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Resolver;

use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\CannotParseArgsException;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Command\Command;

/**
 * A resolved command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ResolvedCommand
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var RawArgs
     */
    private $rawArgs;

    /**
     * @var Args
     */
    private $parsedArgs;

    /**
     * @var CannotParseArgsException
     */
    private $parseError;

    /**
     * @var bool
     */
    private $parsed = false;

    /**
     * Creates a new resolved command.
     *
     * @param Command $command The command.
     * @param RawArgs $rawArgs The raw console arguments.
     */
    public function __construct(Command $command, RawArgs $rawArgs)
    {
        $this->command = $command;
        $this->rawArgs = $rawArgs;
    }

    /**
     * Returns the command.
     *
     * @return Command The command.
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * The raw console arguments.
     *
     * @return RawArgs The raw console arguments.
     */
    public function getRawArgs()
    {
        return $this->rawArgs;
    }

    /**
     * Returns the parsed console arguments.
     *
     * @return Args The parsed console arguments or `null` if the console
     *              arguments cannot be parsed.
     *
     * @see isParsable(), getParseError()
     */
    public function getParsedArgs()
    {
        if (!$this->parsed) {
            $this->parse();
        }

        return $this->parsedArgs;
    }

    /**
     * Returns the error that happened during argument parsing.
     *
     * @return CannotParseArgsException The parse error or `null` if the
     *                                  arguments were parsed successfully.
     *
     * @see isParsable(), getParsedArgs()
     */
    public function getParseError()
    {
        if (!$this->parsed) {
            $this->parse();
        }

        return $this->parseError;
    }

    /**
     * Returns whether the console arguments can be parsed.
     *
     * @return bool Returns `true` if the console arguments can be parsed and
     *              `false` if a parse error occurred.
     *
     * @see getParsedArgs(), getParseError()
     */
    public function isParsable()
    {
        if (!$this->parsed) {
            $this->parse();
        }

        return null === $this->parseError;
    }

    private function parse()
    {
        try {
            $this->parsedArgs = $this->command->parseArgs($this->rawArgs);
        } catch (CannotParseArgsException $e) {
            $this->parseError = $e;
        }

        $this->parsed = true;
    }
}
