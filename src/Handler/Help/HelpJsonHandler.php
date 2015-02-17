<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Handler\Help;

use Symfony\Component\Console\Descriptor\JsonDescriptor;
use Webmozart\Console\Adapter\ApplicationAdapter;
use Webmozart\Console\Adapter\CommandAdapter;
use Webmozart\Console\Adapter\IOAdapter;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Handler\CommandHandler;
use Webmozart\Console\Api\IO\IO;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpJsonHandler implements CommandHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(Command $command, Args $args, IO $io)
    {
        $descriptor = new JsonDescriptor();
        $ioAdapter = new IOAdapter($io);
        $application = $command->getApplication();
        $applicationAdapter = new ApplicationAdapter($application);

        if ($args->isArgumentSet('command')) {
            $theCommand = $application->getCommand($args->getArgument('command'));
            $commandAdapter = new CommandAdapter($theCommand, $applicationAdapter);
            $descriptor->describe($ioAdapter, $commandAdapter);
        } else {
            $descriptor->describe($ioAdapter, $applicationAdapter);
        }

        return 0;
    }
}
