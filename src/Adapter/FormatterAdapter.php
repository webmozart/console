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
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;
use Webmozart\Console\Api\Formatter\Formatter;

/**
 * Adapts a {@link Formatter} instance to Symfony's
 * {@link OutputFormatterInterface} API.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormatterAdapter implements OutputFormatterInterface
{
    /**
     * @var Formatter
     */
    private $adaptedFormatter;

    /**
     * @var bool
     */
    private $decorated = true;

    /**
     * Creates the adapter.
     *
     * @param Formatter $adaptedFormatter The adapted formatter.
     */
    public function __construct(Formatter $adaptedFormatter)
    {
        $this->adaptedFormatter = $adaptedFormatter;
    }

    /**
     * Returns the adapted formatter.
     *
     * @return Formatter The adapted formatter.
     */
    public function getAdaptedFormatter()
    {
        return $this->adaptedFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return $this->decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function setStyle($name, OutputFormatterStyleInterface $style)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function hasStyle($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getStyle($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function format($message)
    {
        if ($this->decorated) {
            return $this->adaptedFormatter->format($message);
        }

        return $this->adaptedFormatter->removeFormat($message);
    }
}
