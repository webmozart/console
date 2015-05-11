<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console;

use Exception;
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Command\CannotAddCommandException;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Event\ConfigEvent;
use Webmozart\Console\Api\Event\ConsoleEvents;
use Webmozart\Console\Api\Event\PreResolveEvent;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Args\ArgvArgs;
use Webmozart\Console\IO\ConsoleIO;
use Webmozart\Console\UI\Component\ExceptionTrace;

/**
 * A console application.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ConsoleApplication implements Application
{
    /**
     * @var ApplicationConfig
     */
    private $config;

    /**
     * @var CommandCollection
     */
    private $commands;

    /**
     * @var CommandCollection
     */
    private $namedCommands;

    /**
     * @var CommandCollection
     */
    private $defaultCommands;

    /**
     * @var ArgsFormat
     */
    private $globalArgsFormat;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ConsoleIO
     */
    private $preliminaryIo;

    /**
     * Creates a new console application.
     *
     * @param ApplicationConfig|callable $config The application configuration
     *                                           or a callable that creates the
     *                                           configuration.
     */
    public function __construct($config)
    {
        $this->preliminaryIo = new ConsoleIO();

        // Enable trace output for exceptions thrown during boot
        $this->preliminaryIo->setVerbosity(IO::VERBOSE);

        if (is_callable($config)) {
            try {
                $config = $config();
            } catch (Exception $e) {
                $trace = new ExceptionTrace($e);
                $trace->render($this->preliminaryIo);

                exit($this->exceptionToExitCode($e->getCode()));
            }
        }

        Assert::isInstanceOf($config, 'Webmozart\Console\Api\Config\ApplicationConfig', 'The $config argument must be an ApplicationConfig or a callable. Got: %s');

        try {
            $dispatcher = $config->getEventDispatcher();

            if ($dispatcher && $dispatcher->hasListeners(ConsoleEvents::CONFIG)) {
                $dispatcher->dispatch(ConsoleEvents::CONFIG,
                    new ConfigEvent($config));
            }

            $this->config = $config;
            $this->dispatcher = $config->getEventDispatcher();
            $this->commands = new CommandCollection();
            $this->namedCommands = new CommandCollection();
            $this->defaultCommands = new CommandCollection();

            $this->globalArgsFormat = new ArgsFormat(array_merge(
                $config->getOptions(),
                $config->getArguments()
            ));

            foreach ($config->getCommandConfigs() as $commandConfig) {
                $this->addCommand($commandConfig);
            }
        } catch (Exception $e) {
            if (!$config->isExceptionCaught()) {
                throw $e;
            }

            // Render the trace to the preliminary IO
            $trace = new ExceptionTrace($e);
            $trace->render($this->preliminaryIo);

            // Ignore isTerminatedAfterRun() setting. This is a fatal error.
            exit($this->exceptionToExitCode($e->getCode()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalArgsFormat()
    {
        return $this->globalArgsFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand($name)
    {
        return $this->commands->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        return clone $this->commands;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCommand($name)
    {
        return $this->commands->contains($name);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCommands()
    {
        return !$this->commands->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedCommands()
    {
        return $this->namedCommands;
    }

    /**
     * {@inheritdoc}
     */
    public function hasNamedCommands()
    {
        return count($this->namedCommands) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultCommands()
    {
        return $this->defaultCommands;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDefaultCommands()
    {
        return count($this->defaultCommands) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveCommand(RawArgs $args)
    {
        if ($this->dispatcher && $this->dispatcher->hasListeners(ConsoleEvents::PRE_RESOLVE)) {
            $event = new PreResolveEvent($args, $this);
            $this->dispatcher->dispatch(ConsoleEvents::PRE_RESOLVE, $event);

            if ($resolvedCommand = $event->getResolvedCommand()) {
                return $resolvedCommand;
            }
        }

        return $this->config->getCommandResolver()->resolveCommand($args, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function run(RawArgs $args = null, Input $input = null, Output $output = null, Output $errorOutput = null)
    {
        // Render errors to the preliminary IO until the final IO is created
        $io = $this->preliminaryIo;

        try {
            if (null === $args) {
                $args = new ArgvArgs();
            }

            $ioFactory = $this->config->getIOFactory();

            if (null === $ioFactory) {
                throw new LogicException('The IO factory must be set.');
            }

            /** @var IO $io */
            $io = call_user_func($ioFactory, $this, $args, $input, $output, $errorOutput);

            $resolvedCommand = $this->resolveCommand($args);
            $command = $resolvedCommand->getCommand();
            $parsedArgs = $resolvedCommand->getArgs();

            $statusCode = $command->handle($parsedArgs, $io);
        } catch (Exception $e) {
            if (!$this->config->isExceptionCaught()) {
                throw $e;
            }

            $trace = new ExceptionTrace($e);
            $trace->render($io);

            $statusCode = $this->exceptionToExitCode($e->getCode());
        }

        if ($this->config->isTerminatedAfterRun()) {
            exit($statusCode);
        }

        return $statusCode;
    }

    /**
     * Converts an exception code to an exit code.
     *
     * @param int $code The exception code.
     *
     * @return int The exit code.
     */
    private function exceptionToExitCode($code)
    {
        if (!is_numeric($code)) {
            return 1;
        }

        return min(max((int) $code, 1), 255);
    }

    private function addCommand(CommandConfig $config)
    {
        if (!$config->isEnabled()) {
            return;
        }

        $this->validateCommandName($config);

        $command = new Command($config, $this);

        $this->commands->add($command);

        if ($config->isDefault()) {
            $this->defaultCommands->add($command);
        }

        if (!$config->isAnonymous()) {
            $this->namedCommands->add($command);
        }
    }

    private function validateCommandName(CommandConfig $config)
    {
        $name = $config->getName();

        if (!$name) {
            throw CannotAddCommandException::nameEmpty();
        }

        if ($this->commands->contains($name)) {
            throw CannotAddCommandException::nameExists($name);
        }
    }
}
