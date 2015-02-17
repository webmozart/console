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

use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Handler\CommandHandler;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Help\ApplicationHelp;
use Webmozart\Console\Rendering\Help\CommandHelp;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpTextHandler implements CommandHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(Command $command, Args $args, IO $io)
    {
        $application = $command->getApplication();
        $canvas = new Canvas($io);

        if ($args->isArgumentSet('command')) {
            $theCommand = $application->getCommand($args->getArgument('command'));
            $usage = new CommandHelp($theCommand);
        } else {
            $usage = new ApplicationHelp($application);
        }

        $usage->render($canvas);
        $canvas->flush();

        return 0;
    }
}
