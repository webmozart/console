<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Handler;

use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Console\Descriptor\DefaultDescriptor;

/**
 * Handler for the "help" command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpHandler extends AbstractHandler
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
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(InputInterface $input)
    {
        $object = $this->getObjectToDescribe($input);

        $descriptor = new DefaultDescriptor();
        $options = array_replace($this->options, array(
            'input' => $input,
            'printCompositeCommands' => $input->getOption('all'),
        ));

        return $descriptor->describe($this->output, $object, $options);
    }

    protected function getObjectToDescribe(InputInterface $input)
    {
        // Describe the command
        if ($input->getArgument('command')) {
            $commandName = $input->getArgument('command');

            if ($input->getArgument('sub-command')) {
                $commandName .= ' '.$input->getArgument('sub-command');
            }

            return $this->command->getApplication()->getCommand($commandName);
        }

        // If no command and no option is set, print the command list
        return $this->command->getApplication();
    }
}
