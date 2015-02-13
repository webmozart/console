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
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
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

            ->addOption('help', 'h', Option::VALUE_NONE, 'Display help about the command')
            ->addOption('quiet', 'q', Option::VALUE_NONE, 'Do not output any message')
            ->addOption('verbose', 'v', Option::VALUE_OPTIONAL, 'Increase the verbosity of messages: "-v" for normal output, "-vv" for more verbose output and "-vvv" for debug', null, 'level')
            ->addOption('version', 'V', Option::VALUE_NONE, 'Display this application version')
            ->addOption('ansi', null, Option::VALUE_NONE, 'Force ANSI output')
            ->addOption('no-ansi', null, Option::VALUE_NONE, 'Disable ANSI output')
            ->addOption('no-interaction', 'n', Option::VALUE_NONE, 'Do not ask any interactive question')

            ->beginCommand('help')
                ->setDescription('Display the manual of a command')
                ->addArgument('command', Argument::OPTIONAL, 'The command name')
                ->addArgument('sub-command', Argument::OPTIONAL, 'The sub command name')
                ->addOption('all', 'a', Option::VALUE_NONE, 'Print all available commands')
                ->addOption('man', 'm', Option::VALUE_NONE, 'Output the help as man page')
                ->addOption('ascii-doc', null, Option::VALUE_NONE, 'Output the help as AsciiDoc document')
                ->addOption('text', 't', Option::VALUE_NONE, 'Output the help as plain text')
                ->addOption('xml', 'x', Option::VALUE_NONE, 'Output the help as XML')
                ->addOption('json', 'j', Option::VALUE_NONE, 'Output the help as JSON')
            ->end()
        ;
    }

}
