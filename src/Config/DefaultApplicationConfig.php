<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Config;

use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputOption;

/**
 * The default application configuration.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultApplicationConfig extends ApplicationConfig
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHelperSet(new HelperSet(array(
                new FormatterHelper(),
                new DebugFormatterHelper(),
                new ProcessHelper(),
                new QuestionHelper(),
            )))

            ->addOption('help', 'h', InputOption::VALUE_NONE, 'Display help about the command')
            ->addOption('quiet', 'q', InputOption::VALUE_NONE, 'Do not output any message')
            ->addOption('verbose', 'v', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug')
            ->addOption('version', 'V', InputOption::VALUE_NONE, 'Display this application version')
            ->addOption('ansi', null, InputOption::VALUE_NONE, 'Force ANSI output')
            ->addOption('no-ansi', null, InputOption::VALUE_NONE, 'Disable ANSI output')
            ->addOption('no-interaction', 'n', InputOption::VALUE_NONE, 'Do not ask any interactive question')

            ->beginCommand('help')
                ->setDescription('Display the manual of a command')
                ->addArgument('command', InputArgument::OPTIONAL, 'The command name')
                ->addArgument('sub-command', InputArgument::OPTIONAL, 'The sub command name')
                ->addOption('all', 'a', InputOption::VALUE_NONE, 'Print all available commands')
                ->addOption('man', 'm', InputOption::VALUE_NONE, 'Output the help as man page')
                ->addOption('ascii-doc', null, InputOption::VALUE_NONE, 'Output the help as AsciiDoc document')
                ->addOption('text', 't', InputOption::VALUE_NONE, 'Output the help as plain text')
                ->addOption('xml', 'x', InputOption::VALUE_NONE, 'Output the help as XML')
                ->addOption('json', 'j', InputOption::VALUE_NONE, 'Output the help as JSON')
            ->end()
        ;
    }

}
