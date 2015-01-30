<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Descriptor\DefaultDescriptor;

/**
 * The "help" command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpCommand extends Command
{
    /**
     * @var array
     */
    private $options;

    /**
     * Creates the command.
     *
     * @param array $options The options passed to the descriptor by default.
     */
    public function __construct(array $options = array())
    {
        parent::__construct();

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('help')
            ->setDescription('Display the manual of a command')
            ->addArgument('command', InputArgument::OPTIONAL, 'The command name')
            ->addArgument('sub-command', InputArgument::OPTIONAL, 'The sub command name')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Print all available commands')
            ->addOption('man', 'm', InputOption::VALUE_NONE, 'Output the help as man page')
            ->addOption('ascii-doc', null, InputOption::VALUE_NONE, 'Output the help as AsciiDoc document')
            ->addOption('text', 't', InputOption::VALUE_NONE, 'Output the help as plain text')
            ->addOption('xml', 'x', InputOption::VALUE_NONE, 'Output the help as XML')
            ->addOption('json', 'j', InputOption::VALUE_NONE, 'Output the help as JSON')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $object = $this->parseObject($input);

        $descriptor = new DefaultDescriptor();
        $options = array_replace($this->options, array(
            'input' => $input,
            'printCompositeCommands' => $input->getOption('all'),
        ));

        return $descriptor->describe($output, $object, $options);
    }

    protected function parseObject(InputInterface $input)
    {
        // Describe the command
        if ($input->getArgument('command')) {
            $commandName = $input->getArgument('command');

            if ($input->getArgument('sub-command')) {
                $commandName .= ' '.$input->getArgument('sub-command');
            }

            return $this->getApplication()->get($commandName);
        }

        // If no command and no option is set, print the command list
        return $this->getApplication();
    }
}
