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

use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Event\ConsoleEvents;
use Webmozart\Console\Api\Event\PreHandleEvent;
use Webmozart\Console\Api\Event\PreResolveEvent;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Api\Resolver\ResolvedCommand;
use Webmozart\Console\Formatter\AnsiFormatter;
use Webmozart\Console\Formatter\PlainFormatter;
use Webmozart\Console\Handler\Help\HelpHandler;
use Webmozart\Console\IO\ConsoleIO;
use Webmozart\Console\IO\Input\StandardInput;
use Webmozart\Console\IO\Output\ErrorOutput;
use Webmozart\Console\IO\Output\StandardOutput;
use Webmozart\Console\UI\Component\NameVersion;

/**
 * The default application configuration.
 *
 * @since  1.0
 *
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
            ->setIOFactory(array($this, 'createIO'))
            ->addEventListener(ConsoleEvents::PRE_RESOLVE, array($this, 'resolveHelpCommand'))
            ->addEventListener(ConsoleEvents::PRE_HANDLE, array($this, 'printVersion'))

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

    public function createIO(Application $application, RawArgs $args, Input $input = null, Output $output = null, Output $errorOutput = null)
    {
        $input = $input ?: new StandardInput();
        $output = $output ?: new StandardOutput();
        $errorOutput = $errorOutput ?: new ErrorOutput();
        $styleSet = $application->getConfig()->getStyleSet();

        if ($args->hasToken('--no-ansi')) {
            $formatter = new PlainFormatter($styleSet);
        } elseif ($args->hasToken('--ansi')) {
            $formatter = new AnsiFormatter($styleSet);
        } else {
            $formatter = $output->supportsAnsi() ? new AnsiFormatter($styleSet) : new PlainFormatter($styleSet);
        }

        $io = new ConsoleIO($input, $output, $errorOutput, $formatter);

        if ($args->hasToken('-vvv') || $this->isDebug()) {
            $io->setVerbosity(IO::DEBUG);
        } elseif ($args->hasToken('-vv')) {
            $io->setVerbosity(IO::VERY_VERBOSE);
        } elseif ($args->hasToken('-v')) {
            $io->setVerbosity(IO::VERBOSE);
        }

        if ($args->hasToken('--quiet') || $args->hasToken('-q')) {
            $io->setQuiet(true);
        }

        if ($args->hasToken('--no-interaction') || $args->hasToken('-n')) {
            $io->setInteractive(false);
        }

        return $io;
    }

    public function resolveHelpCommand(PreResolveEvent $event)
    {
        $args = $event->getRawArgs();

        if ($args->hasToken('-h') || $args->hasToken('--help')) {
            $command = $event->getApplication()->getCommand('help');

            // Enable lenient args parsing
            $parsedArgs = $command->parseArgs($args, true);

            $event->setResolvedCommand(new ResolvedCommand($command, $parsedArgs));
            $event->stopPropagation();
        }
    }

    public function printVersion(PreHandleEvent $event)
    {
        if ($event->getArgs()->isOptionSet('version')) {
            $version = new NameVersion($event->getCommand()->getApplication()->getConfig());
            $version->render($event->getIO());

            $event->setHandled(true);
        }
    }
}
