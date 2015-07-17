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
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Resolver\ResolvedCommand;

/**
 * Dispatched before the console arguments are resolved to a command.
 *
 * Add a listener for this event to customize the command used for the given
 * console arguments.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PreResolveEvent extends Event
{
    /**
     * @var RawArgs
     */
    private $rawArgs;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var ResolvedCommand
     */
    private $resolvedCommand;

    /**
     * Creates the event.
     *
     * @param RawArgs     $rawArgs     The raw console arguments.
     * @param Application $application The application.
     */
    public function __construct(RawArgs $rawArgs, Application $application)
    {
        $this->rawArgs = $rawArgs;
        $this->application = $application;
    }

    /**
     * Returns the raw console arguments.
     *
     * @return RawArgs The raw console arguments.
     */
    public function getRawArgs()
    {
        return $this->rawArgs;
    }

    /**
     * Returns the application.
     *
     * @return Application The application.
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Returns the resolved command.
     *
     * @return ResolvedCommand Returns the resolved command or `null` if none
     *                         was set.
     */
    public function getResolvedCommand()
    {
        return $this->resolvedCommand;
    }

    /**
     * Sets the resolved command.
     *
     * @param ResolvedCommand $resolvedCommand The resolved command. Set to
     *                                         `null` to let the configured
     *                                         resolver decide.
     */
    public function setResolvedCommand(ResolvedCommand $resolvedCommand = null)
    {
        $this->resolvedCommand = $resolvedCommand;
    }
}
