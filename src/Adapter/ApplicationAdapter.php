<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Adapter;

use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Adapts the command API of this package to Symfony's {@link Application} API.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ApplicationAdapter extends Application
{
    /**
     * @var \Webmozart\Console\Api\Application\Application
     */
    private $application;

    /**
     * @var CommandAdapter
     */
    private $currentCommand;

    /**
     * Creates the application.
     *
     * @param \Webmozart\Console\Api\Application\Application $application
     */
    public function __construct(\Webmozart\Console\Api\Application\Application $application)
    {
        $this->application = $application;

        $config = $application->getConfig();
        $terminalDimensions = $config->getTerminalDimensions();

        parent::__construct($config->getName(), $config->getVersion());

        if ($config->getDispatcher()) {
            $this->setDispatcher($config->getDispatcher());
        }

        $this->setAutoExit($config->isTerminatedAfterRun());
        $this->setCatchExceptions($config->isExceptionCaught());
        $this->setTerminalDimensions($terminalDimensions->getWidth(), $terminalDimensions->getHeight());

        foreach ($application->getCommands() as $command) {
            $this->add(new CommandAdapter($command));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $commandResolver = $this->application->getConfig()->getCommandResolver();
        $command = $commandResolver->resolveCommand($input, $this->application->getCommands());

        $this->currentCommand = $this->get($command->getName());

        try {
            $result = parent::doRun($input, $output);
            $this->currentCommand = null;
        } catch (Exception $e) {
            $this->currentCommand = null;

            throw $e;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        // This method must return something, otherwise the base class tries
        // to set the "command" argument which doesn't usually exist
        return 'command-name';
    }

    /**
     * {@inheritdoc}
     */
    public function find($name)
    {
        return $this->currentCommand;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinitionAdapter($this->application->getBaseInputDefinition());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHelperSet()
    {
        return $this->application->getConfig()->getHelperSet();
    }
}
