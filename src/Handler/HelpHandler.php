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

use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Input\Input;
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
    public function handle(Args $args, Input $input)
    {
        $object = $this->getObjectToDescribe($args);

        $descriptor = new DefaultDescriptor();
        $options = array_replace($this->options, array(
            'input' => $input,
            'printCompositeCommands' => $args->getOption('all'),
        ));

        return $descriptor->describe($this->output, $object, $options);
    }

    protected function getObjectToDescribe(Args $args)
    {
        // Describe the command
        if ($args->getArgument('command')) {
            $commandName = $args->getArgument('command');

            if ($args->getArgument('sub-command')) {
                $commandName .= ' '.$args->getArgument('sub-command');
            }

            return $this->command->getApplication()->getCommand($commandName);
        }

        // If no command and no option is set, print the command list
        return $this->command->getApplication();
    }
}
