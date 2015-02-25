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

use Symfony\Component\EventDispatcher\Event;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\IO\IO;

/**
 * The event dispatched for {@link ConsoleEvents::PRE_HANDLE}.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PreHandleEvent extends Event
{
    /**
     * @var Args
     */
    private $args;

    /**
     * @var IO
     */
    private $io;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var bool
     */
    private $handled = false;

    /**
     * @var int
     */
    private $statusCode = 0;

    /**
     * Creates the event.
     *
     * @param Args    $args    The parsed console arguments.
     * @param IO      $io      The I/O.
     * @param Command $command The executed command.
     */
    public function __construct(Args $args, IO $io, Command $command)
    {
        $this->args = $args;
        $this->io = $io;
        $this->command = $command;
    }

    /**
     * Returns the parsed console arguments.
     *
     * @return Args The parsed console arguments.
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Returns the I/O.
     *
     * @return IO The I/O.
     */
    public function getIO()
    {
        return $this->io;
    }

    /**
     * Returns the executed command.
     *
     * @return Command The executed command.
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Returns whether the command was handled by the event listener.
     *
     * @return boolean Returns `true` if the command was handled and `false`
     *                 otherwise.
     *
     * @see setHandled()
     */
    public function isHandled()
    {
        return $this->handled;
    }

    /**
     * Sets whether the command was handled by the event listener.
     *
     * If set to `true`, the handler configured for the command is not
     * executed. Instead the status code returned by {@link getStatusCode()}
     * is returned.
     *
     * @param boolean $handled Whether the command was handled by the event
     *                         listener.
     */
    public function setHandled($handled)
    {
        $this->handled = (bool) $handled;
    }

    /**
     * Returns the status code to return.
     *
     * @return int Returns 0 on success and any positive integer on error.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the status code to return.
     *
     * This method is only useful in combination with {@link setHandled()}.
     * If the event is not marked as handled, the status code is ignored.
     *
     * @param int $statusCode Set to 0 on success and any positive integer on
     *                        error.
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = (int) $statusCode;
    }
}
