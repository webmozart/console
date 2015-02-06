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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Adapts the command API of this package to Symfony's {@link Command} API.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandAdapter extends Command
{
    /**
     * @var \Webmozart\Console\Api\Command\Command
     */
    private $adaptedCommand;

    /**
     * Creates the adapter.
     *
     * @param \Webmozart\Console\Api\Command\Command $adaptedCommand The adapted command.
     */
    public function __construct(\Webmozart\Console\Api\Command\Command $adaptedCommand)
    {
        parent::__construct($adaptedCommand->getName());

        $this->adaptedCommand = $adaptedCommand;
    }

    /**
     * Returns the adapted command.
     *
     * @return \Webmozart\Console\Api\Command\Command The adapted command.
     */
    public function getAdaptedCommand()
    {
        return $this->adaptedCommand;
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input  The console input.
     * @param OutputInterface $output The console output.
     *
     * @return int The exit status.
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $commandConfig = $this->adaptedCommand->getConfig();
        $errorOutput = $output instanceof ConsoleOutput ? $output->getErrorOutput() : $output;

        $inputDefinition = new InputDefinitionAdapter($this->adaptedCommand->getInputDefinition());

        // Bind the input to the input definition of the command
        $input->bind($inputDefinition);

        // Set the command names in case they are missing from the input
        // This happens if a default command is executed
        $this->setCommandNames($input, $inputDefinition);

        // Adjust the title of the process
        if (null !== $commandConfig->getProcessTitle()) {
            $this->setTitleOfCurrentProcess($commandConfig->getProcessTitle(), $errorOutput);
        }

        // Set more arguments/options interactively
        if ($input->isInteractive()) {
            $commandConfig->interact($input);
        }

        // Now validate the input
        $input->validate();

        // Delegate to the command handler
        $commandHandler = $commandConfig->getHandler($this->adaptedCommand);
        $commandHandler->initialize($this->adaptedCommand, $output, $errorOutput);

        $statusCode = $commandHandler->handle($input);

        return is_numeric($statusCode) ? (int) $statusCode : 0;
    }

    private function setCommandNames(InputInterface $input, InputDefinitionAdapter $inputDefinition)
    {
        foreach ($inputDefinition->getCommandNames() as $argName => $commandName) {
            $input->setArgument($argName, $commandName);
        }
    }

    private function setTitleOfCurrentProcess($processTitle, OutputInterface $errorOutput)
    {
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($processTitle);
        } elseif (function_exists('setproctitle')) {
            setproctitle($processTitle);
        } elseif (OutputInterface::VERBOSITY_VERY_VERBOSE === $errorOutput->getVerbosity()) {
            $errorOutput->writeln('<comment>Install the proctitle PECL to be able to change the process title.</comment>');
        }
    }
}
