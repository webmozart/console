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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Command\Command;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TestPackageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('package')
            ->setAliases(array('package-alias'))
            ->setDescription('Description of "package"')
            ->addArgument('arg', InputArgument::OPTIONAL, 'The "arg" argument')
            ->addOption('option', 'o', InputOption::VALUE_NONE, 'The "option" option')
            ->addOption('value', 'v', InputOption::VALUE_REQUIRED, 'The "value" option')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arg = $input->getArgument('arg');
        $opt = $input->getOption('option');
        $val = $input->getOption('value');

        $output->write('"package'.($opt ? ' -o' : '').($val ? ' -v'.$val : '').($arg ? ' '.$arg : '').'" executed');
    }
}
