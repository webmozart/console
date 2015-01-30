<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\CommandHandler;

use Webmozart\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractCommandHandler implements CommandHandler
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var OutputInterface
     */
    protected $errorOutput;

    /**
     * @var Command
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    public function initialize(Command $command, OutputInterface $output)
    {
        $this->command = $command;
        $this->output = $output;
        $this->errorOutput = $output;

        if ($output instanceof ConsoleOutput) {
            $this->errorOutput = $output->getErrorOutput();
        }

        return $this;
    }

    /**
     * Handles a console input.
     *
     * @param InputInterface $input The console input.
     *
     * @return int The result code.
     */
    public function handle(InputInterface $input)
    {
        // force the creation of the synopsis before the merge with the app definition
        $this->command->getSynopsis();

        // add the application arguments and options
        $this->command->mergeApplicationDefinition();

        // bind the input against the command specific arguments/options
        $input->bind($this->command->getDefinition());

        if (null !== $this->command->getProcessTitle()) {
            if (function_exists('cli_set_process_title')) {
                cli_set_process_title($this->command->getProcessTitle());
            } elseif (function_exists('setproctitle')) {
                setproctitle($this->command->getProcessTitle());
            } elseif (OutputInterface::VERBOSITY_VERY_VERBOSE === $this->output->getVerbosity()) {
                $this->errorOutput->writeln('<comment>Install the proctitle PECL to be able to change the process title.</comment>');
            }
        }

        if ($input->isInteractive()) {
            $this->interact($input);
        }

        $input->validate();

        $statusCode = $this->execute($input);

        return is_numeric($statusCode) ? (int) $statusCode : 0;
    }

    abstract protected function execute(InputInterface $input);

    protected function interact(InputInterface $input)
    {
    }
}
