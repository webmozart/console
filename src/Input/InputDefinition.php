<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Input;

use Webmozart\Console\Command\Command;
use Webmozart\Console\Command\CompositeCommand;

/**
 * An input definition with a tweaked synopsis.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputDefinition extends \Symfony\Component\Console\Input\InputDefinition
{
    public function addOption(\Symfony\Component\Console\Input\InputOption $option)
    {
        if (!$option instanceof InputOption) {
            if ($option->isValueOptional()) {
                $mode = InputOption::VALUE_OPTIONAL;
            } elseif ($option->isValueRequired()) {
                $mode = InputOption::VALUE_REQUIRED;
            } else {
                $mode = InputOption::VALUE_NONE;
            }

            if ($option->isArray()) {
                $mode |= InputOption::VALUE_IS_ARRAY;
            }

            $option = new InputOption(
                $option->getName(),
                $option->getShortcut(),
                $mode,
                $option->getDescription(),
                $option->acceptValue() ? $option->getDefault() : null
            );
        }

        parent::addOption($option);
    }

    /**
     * {@inheritdoc}
     */
    public function getSynopsis()
    {
        $elements = array();

        foreach ($this->getOptions() as $option) {
            // \xC2\xA0 is a non-breaking space
            if ($option->isValueRequired()) {
                $format = "--%s\xC2\xA0<%s>";
            } elseif ($option->isValueOptional()) {
                $format = "--%s\xC2\xA0[<%s>]";
            } else {
                $format = '--%s';
            }

            $elements[] = sprintf('['.$format.']', $option->getName(), $option->getValueName());
        }

        foreach ($this->getArguments() as $argument) {
            $name = $argument->getName();

            if (in_array($name, array(Command::COMMAND_ARG, CompositeCommand::SUB_COMMAND_ARG))) {
                continue;
            }

            $elements[] = sprintf(
                $argument->isRequired() ? '<%s>' : '[<%s>]',
                $name.($argument->isArray() ? '1' : '')
            );

            if ($argument->isArray()) {
                $elements[] = sprintf('... [<%sN>]', $name);
            }
        }

        return implode(' ', $elements);
    }
}
