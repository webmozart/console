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
use Webmozart\Console\Api\Output\Dimensions;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Api\Style\StyleSet;

/**
 * An output that wraps the standard and the error output.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CompositeOutput implements Output
{
    /**
     * @var Output
     */
    private $output;

    /**
     * @var Output
     */
    private $errorOutput;

    /**
     * Creates a new composite output.
     *
     * @param Output $output      The standard output.
     * @param Output $errorOutput The error output.
     */
    public function __construct(Output $output, Output $errorOutput)
    {
        $this->output = $output;
        $this->errorOutput = $errorOutput;
    }

    /**
     * Returns the standard output.
     *
     * @return Output The standard output.
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Returns the error output.
     *
     * @return Output The error output.
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function setDimensions(Dimensions $dimensions)
    {
        $this->output->setDimensions($dimensions);
        $this->errorOutput->setDimensions($dimensions);
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions()
    {
        return $this->output->getDimensions();
    }

    /**
     * {@inheritdoc}
     */
    public function setStyleSet(StyleSet $styleSet)
    {
        $this->output->setStyleSet($styleSet);
        $this->errorOutput->setStyleSet($styleSet);
    }

    /**
     * {@inheritdoc}
     */
    public function getStyleSet()
    {
        return $this->output->getStyleSet();
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        $this->output->write($messages, $newline, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        $this->output->writeln($messages, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        $this->output->setVerbosity($level);
        $this->errorOutput->setVerbosity($level);
    }

    /**
     * {@inheritdoc}
     */
    public function getVerbosity()
    {
        return $this->output->getVerbosity();
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        $this->output->setDecorated($decorated);
        $this->errorOutput->setDecorated($decorated);
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return $this->output->isDecorated();
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->output->setFormatter($formatter);
        $this->errorOutput->setFormatter($formatter);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        return $this->output->getFormatter();
    }
}
