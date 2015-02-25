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
use Webmozart\Console\Handler\Help\HelpHandler;

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

            ->addOption('help', 'h', Option::NO_VALUE, 'Display help about the command')
            ->addOption('quiet', 'q', Option::NO_VALUE, 'Do not output any message')
            ->addOption('verbose', 'v', Option::OPTIONAL_VALUE, 'Increase the verbosity of messages: "-v" for normal output, "-vv" for more verbose output and "-vvv" for debug', null, 'level')
            ->addOption('version', 'V', Option::NO_VALUE, 'Display this application version')
            ->addOption('ansi', null, Option::NO_VALUE, 'Force ANSI output')
            ->addOption('no-ansi', null, Option::NO_VALUE, 'Disable ANSI output')
            ->addOption('no-interaction', 'n', Option::NO_VALUE, 'Do not ask any interactive question')

            ->beginCommand('help')
                ->markDefault()
                ->setDescription('Display the manual of a command')
                ->addArgument('command', Argument::OPTIONAL, 'The command name')
                ->addOption('man', 'm', Option::NO_VALUE, 'Output the help as man page')
                ->addOption('ascii-doc', null, Option::NO_VALUE, 'Output the help as AsciiDoc document')
                ->addOption('text', 't', Option::NO_VALUE, 'Output the help as plain text')
                ->addOption('xml', 'x', Option::NO_VALUE, 'Output the help as XML')
                ->addOption('json', 'j', Option::NO_VALUE, 'Output the help as JSON')
                ->setHandler(function () { return new HelpHandler(); })
            ->end()
        ;
    }

}
