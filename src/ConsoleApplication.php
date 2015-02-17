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

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Webmozart\Console\Adapter\ApplicationAdapter;
use Webmozart\Console\Adapter\ArgsAdapter;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NamedCommand;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Args\ArgvArgs;
use Webmozart\Console\Adapter\IOAdapter;
use Webmozart\Console\Formatter\AnsiFormatter;
use Webmozart\Console\IO\FormattedIO;
use Webmozart\Console\IO\Input\StandardInput;
use Webmozart\Console\IO\Output\ErrorOutput;
use Webmozart\Console\IO\Output\StandardOutput;
use Webmozart\Console\IO\RawIO;

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
     * @var Command[]
     */
    private $unnamedCommands = array();

    /**
     * @var Command[]
     */
    private $defaultCommands = array();

    /**
     * @var ArgsFormat
     */
    private $globalArgsFormat;

    /**
     * @var ApplicationAdapter
     */
    private $applicationAdapter;

    /**
     * Creates a new console application.
     *
     * @param ApplicationConfig $config The application configuration.
     */
    public function __construct(ApplicationConfig $config)
    {
        $this->config = $config;
        $this->commands = new CommandCollection();

        $this->globalArgsFormat = new ArgsFormat(array_merge(
            $config->getOptions(),
            $config->getArguments()
        ));

        foreach ($config->getCommandConfigs() as $commandConfig) {
            if ($commandConfig->isEnabled()) {
                $this->commands->add(new NamedCommand($commandConfig, $this));
            }
        }

        foreach ($config->getUnnamedCommandConfigs() as $commandConfig) {
            $this->unnamedCommands[] = $this->defaultCommands[] = new Command($commandConfig, $this);
        }

        foreach ($config->getDefaultCommands() as $commandName) {
            $this->defaultCommands[] = $this->commands->get($commandName);
        }

        $this->applicationAdapter = new ApplicationAdapter($this);
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
    public function getUnnamedCommands()
    {
        return $this->unnamedCommands;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUnnamedCommands()
    {
        return count($this->unnamedCommands) > 0;
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
    public function run(RawArgs $args = null, Input $input = null, Output $output = null, Output $errorOutput = null)
    {
        if (null === $args) {
            $args = new ArgvArgs();
        }

        if (null === $input) {
            $input = new StandardInput();
        }

        if (null === $output) {
            $output = new StandardOutput();
        }

        if (null === $errorOutput) {
            $errorOutput = new ErrorOutput();
        }

        $io = new FormattedIO($input, $output, $errorOutput, new AnsiFormatter());

        return $this->applicationAdapter->run(new ArgsAdapter($args), new IOAdapter($io));
    }
}
