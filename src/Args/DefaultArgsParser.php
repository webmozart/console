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
        $this->ensureCommandNamesSet($formatAdapter);

        try {
            $this->validate();
        } catch (RuntimeException $e) {
            throw new CannotParseArgsException($e->getMessage());
        }

        return $this->createArgs($format);
    }

    /**
     * Ensures that the command name arguments are set.
     *
     * This is necessary to prevent {@link validate()} from failing if not all
     * command names are included in the raw args.
     *
     * @param ArgsFormatAdapter $formatAdapter The format adapter.
     */
    private function ensureCommandNamesSet(ArgsFormatAdapter $formatAdapter)
    {
        foreach ($formatAdapter->getCommandNamesByArgumentName() as $name => $value) {
            $this->arguments[$name] = $value;
        }
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
}
