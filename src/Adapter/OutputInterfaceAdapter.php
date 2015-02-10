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
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Api\Output\Dimensions;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Api\Style\StyleSet;
use Webmozart\Console\Style\DefaultStyleSet;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class OutputInterfaceAdapter implements Output, ConsoleOutputInterface
{
    /**
     * @var OutputInterface
     */
    private $adaptedOutput;

    /**
     * @var OutputFormatterInterface
     */
    private $baseFormatter;

    /**
     * @var Dimensions
     */
    private $dimensions;

    /**
     * @var StyleSet
     */
    private $styleSet;

    /**
     * @var OutputInterface
     */
    private $errorOutput;

    public function __construct(OutputInterface $output, Dimensions $dimensions = null)
    {
        $this->adaptedOutput = $output;
        $this->errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $this->dimensions = $dimensions ?: Dimensions::forCurrentWindow();
        $this->baseFormatter = $output->getFormatter();
        $this->styleSet = new DefaultStyleSet();

        $this->refreshFormatter();
    }

    /**
     * @return OutputInterface
     */
    public function getAdaptedOutput()
    {
        return $this->adaptedOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorOutput(OutputInterface $errorOutput)
    {
        $this->errorOutput = $errorOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function setDimensions(Dimensions $dimensions)
    {
        $this->dimensions = $dimensions;
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * {@inheritdoc}
     */
    public function setStyleSet(StyleSet $styleSet)
    {
        $this->styleSet = $styleSet;

        $this->refreshFormatter();
    }

    /**
     * {@inheritdoc}
     */
    public function getStyleSet()
    {
        return $this->styleSet;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        return $this->adaptedOutput->write($messages, $newline, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        return $this->adaptedOutput->writeln($messages, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        return $this->adaptedOutput->setVerbosity($level);
    }

    /**
     * {@inheritdoc}
     */
    public function getVerbosity()
    {
        return $this->adaptedOutput->getVerbosity();
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        return $this->adaptedOutput->setDecorated($decorated);
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return $this->adaptedOutput->isDecorated();
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->baseFormatter = $formatter;

        $this->refreshFormatter();
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        return $this->adaptedOutput->getFormatter();
    }

    private function refreshFormatter()
    {
        $styledFormatter = clone $this->baseFormatter;

        foreach ($this->styleSet->getStyles() as $name => $style) {
            $styledFormatter->setStyle($name, $style);
        }

        $this->adaptedOutput->setFormatter($styledFormatter);
    }
}
