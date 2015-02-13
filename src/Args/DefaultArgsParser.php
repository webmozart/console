<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Args;

use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Webmozart\Console\Adapter\ArgsFormatAdapter;
use Webmozart\Console\Api\Args\CannotParseArgsException;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\ArgsParser;
use Webmozart\Console\Api\Args\RawArgs;

/**
 * Default parser for {@link RawArgs} instances.
 *
 * This parser delegates most of the work to Symfony's {@link ArgvInput} class.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultArgsParser extends ArgvInput implements ArgsParser
{
    /**
     * Creates a new parser.
     */
    public function __construct()
    {
        // Hide the parent arguments from the public signature
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function parseArgs(RawArgs $args, ArgsFormat $format)
    {
        $this->setTokens($args->getTokens());

        $formatAdapter = new ArgsFormatAdapter($format);

        try {
            $this->bind($formatAdapter);
        } catch (RuntimeException $e) {
            throw new CannotParseArgsException($e->getMessage());
        }

        // Prevent failing validation if not all command names are given
        $this->insertMissingCommandNames($formatAdapter);

        return $this->createArgs($format);
    }

    /**
     * Creates the arguments from the current class state.
     *
     * @param ArgsFormat $format The format.
     *
     * @return Args The created console arguments.
     */
    private function createArgs(ArgsFormat $format)
    {
        $args = new Args($format);

        foreach ($this->arguments as $name => $value) {
            // Filter command names
            if ($format->hasArgument($name)) {
                $args->setArgument($name, $value);
            }
        }

        foreach ($this->options as $name => $value) {
            // Filter command options
            if ($format->hasOption($name)) {
                $args->setOption($name, $value);
            }
        }

        return $args;
    }

    private function insertMissingCommandNames(ArgsFormatAdapter $formatAdapter)
    {
        // Start with the default values of the arguments.
        $inputArguments = $formatAdapter->getArguments();
        $fixedValues = array();
        $commandNames = $formatAdapter->getCommandNamesByArgumentName();

        // Flatten the actual arguments, in case they contain a multi-valued
        // argument.
        $actualValues = $this->flatten($this->arguments);

        // Reset all array pointers.
        reset($commandNames);
        reset($actualValues);
        reset($inputArguments);

        // Skip the command names. The resulting pointer is like this:
        //
        // actual: [ 0: remote, 1: origin, 2: foo/bar ]
        //                      ^

        $this->skipCommandNames($actualValues, $commandNames);

        // Copy the command names into the fixed array. The result is:
        //
        // fixed: [ cmd1: remote, cmd2: add ]

        $this->copyArgumentValues($commandNames, $inputArguments, $fixedValues);

        // Copy the remaining actual values. The result is:
        //
        // fixed: [ cmd1: remote, cmd2: add, name: origin, target: foo/bar ]

        $this->copyArgumentValues($actualValues, $inputArguments, $fixedValues);

        // Overwrite all current arguments with the fixed values
        foreach ($fixedValues as $name => $value) {
            $this->arguments[$name] = $value;
        }
    }

    private function flatten(array $arguments, array &$result = array())
    {
        foreach ($arguments as $value) {
            if (is_array($value)) {
                $this->flatten($value, $result);
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    private function skipCommandNames(array &$arguments, array $commandNames)
    {
        reset($commandNames);

        while (null !== key($arguments) && null !== key($commandNames) && current($arguments) === current($commandNames)) {
            next($arguments);
            next($commandNames);
        }
    }

    private function copyArgumentValues(array &$actualValues, array &$inputArguments, array &$fixedValues)
    {
        // The starting point are two arrays of arguments with the array
        // pointers set:

        // values: [ 0: remote, 1: origin, 2: foo/bar ]
        //                      ^
        // args:   [ cmd1: Argument, cmd2: Argument, name: Argument, target: Argument ]
        //                                                     ^

        // The fixed values may already contain values:

        // fixed: [ cmd1: remote, cmd2: add ]

        // The goal is to copy the actual values to the fixed array so that the
        // end result is:

        // fixed: [ cmd1: remote, cmd2: add, name: origin, target: foo/bar ]

        // Multi-valued arguments need special treatment. In this case, the
        // actual values are like this:

        // values: [ 0: remote, 1: one, 2: two, 3: three ]
        //                      ^
        // args:   [ cmd1: Argument, cmd2: Argument, multi: Argument ]
        //                                                     ^

        // The expected result is:

        // fixed: [ cmd1: remote, cmd2: add, multi: [ one, two, three ] ]

        while (null !== key($actualValues)) {
            if (null === key($inputArguments)) {
                throw new CannotParseArgsException('Too many arguments.');
            }

            /** @var InputArgument $argument */
            $argument = current($inputArguments);
            $name = $argument->getName();
            $value = current($actualValues);

            // Append the value to multi-valued arguments
            if ($argument->isArray()) {
                if (!isset($fixedValues[$name])) {
                    $fixedValues[$name] = array();
                }

                $fixedValues[$name][] = $value;

                // The multi-valued argument is the last one, so we don't
                // need to advance the array pointer anymore.
            } else {
                $fixedValues[$name] = $value;

                next($inputArguments);
            }

            next($actualValues);
        }
    }
}
