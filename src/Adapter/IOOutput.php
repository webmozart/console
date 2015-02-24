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

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\IO\RawIO;

/**
 * Adapts an {@link IO} instance to Symfony's {@link OutputInterface} API.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class IOOutput implements OutputInterface
{
    /**
     * @var IO
     */
    private $io;

    /**
     * Creates a new composite output.
     *
     * @param IO $io The I/O.
     */
    public function __construct(IO $io)
    {
        $this->io = $io;
    }

    /**
     * Returns the standard output.
     *
     * @return IO The standard output.
     */
    public function getIO()
    {
        return $this->io;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        foreach ((array) $messages as $message) {
            if ($newline) {
                $this->doWriteLine($message, $type);
            } else {
                $this->doWrite($message, $type);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        foreach ((array) $messages as $message) {
            $this->doWriteLine($message, $type);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        if (!$this->io instanceof RawIO) {
            return;
        }

        switch ($level) {
            case self::VERBOSITY_QUIET:
                $this->io->setQuiet(true);
                break;
            case self::VERBOSITY_NORMAL:
                $this->io->setQuiet(false);
                $this->io->setVerbosity(IO::NORMAL);
                break;
            case self::VERBOSITY_VERBOSE:
                $this->io->setQuiet(false);
                $this->io->setVerbosity(IO::VERBOSE);
                break;
            case self::VERBOSITY_VERY_VERBOSE:
                $this->io->setQuiet(false);
                $this->io->setVerbosity(IO::VERY_VERBOSE);
                break;
            case self::VERBOSITY_DEBUG:
                $this->io->setQuiet(false);
                $this->io->setVerbosity(IO::DEBUG);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVerbosity()
    {
        if ($this->io->isQuiet()) {
            return self::VERBOSITY_QUIET;
        }

        if ($this->io->isDebug()) {
            return self::VERBOSITY_DEBUG;
        }

        if ($this->io->isVeryVerbose()) {
            return self::VERBOSITY_VERY_VERBOSE;
        }

        if ($this->io->isVerbose()) {
            return self::VERBOSITY_VERBOSE;
        }

        return self::VERBOSITY_NORMAL;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        return new FormatterAdapter($this->io);
    }

    private function doWriteLine($message, $type)
    {
        switch ($type) {
            case self::OUTPUT_PLAIN:
                $this->io->writeLine($this->io->removeFormat($message));
                break;
            case self::OUTPUT_RAW:
                $this->io->writeLineRaw($message);
                break;
            default:
                $this->io->writeLine($message);
                break;
        }
    }

    private function doWrite($message, $type)
    {
        switch ($type) {
            case self::OUTPUT_PLAIN:
                $this->io->write($this->io->removeFormat($message));
                break;
            case self::OUTPUT_RAW:
                $this->io->writeRaw($message);
                break;
            default:
                $this->io->write($message);
                break;
        }
    }
}
