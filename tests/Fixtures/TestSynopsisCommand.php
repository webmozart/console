<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Fixtures;

use Symfony\Component\Console\Input\InputArgument;
use Webmozart\Console\Command\Command;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TestSynopsisCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('synopsis')
            ->setDescription('Description of "synopsis"')
            ->addArgument('arg', InputArgument::OPTIONAL, 'The "arg" argument')
            ->addSynopsis('<arg>')
            ->addSynopsis('[--foo] [--bar]')
        ;
    }
}
