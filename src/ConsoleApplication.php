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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Adapter\ApplicationAdapter;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Input\InputDefinition;

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
     * @var InputDefinition
     */
    private $baseDefinition;

    /**
     * @var ApplicationAdapter
     */
    private $applicationAdapter;

    /**
     * Creates the console runner.
     *
     * @param ApplicationConfig $config The application configuration.
     */
    public function __construct(ApplicationConfig $config)
    {
        $this->config = $config;
        $this->commands = new CommandCollection();

        $this->baseDefinition = new InputDefinition(array_merge(
            $config->getOptions(),
            $config->getArguments()
        ));

        foreach ($config->getCommandConfigs() as $commandConfig) {
            if ($commandConfig->isEnabled()) {
                $this->commands->add(new Command($commandConfig, $this->baseDefinition, $this));
            }
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
    public function getBaseInputDefinition()
    {
        return $this->baseDefinition;
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
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return $this->applicationAdapter->run($input, $output);
    }
}
