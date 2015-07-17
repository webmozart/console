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

use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\RawArgs;

/**
 * Returns the command to execute for the given console arguments.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface CommandResolver
{
    /**
     * Returns the command to execute for the given console arguments.
     *
     * @param RawArgs     $args        The console arguments.
     * @param Application $application The application.
     *
     * @return ResolvedCommand The command to execute.
     *
     * @throws CannotResolveCommandException If the command cannot be resolved.
     */
    public function resolveCommand(RawArgs $args, Application $application);
}
