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

use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;

/**
 * Returns the command to execute for a console input.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface CommandResolver
{
    /**
     * Returns the command to execute for a console input.
     *
     * @param InputInterface    $input    The console input.
     * @param CommandCollection $commands The available commands.
     *
     * @return Command The command to execute.
     */
    public function resolveCommand(InputInterface $input, CommandCollection $commands);
}
