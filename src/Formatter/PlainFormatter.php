<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Webmozart\Console\Adapter\StyleConverter;
use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Api\Formatter\StyleSet;

/**
 * A formatter that removes all format tags.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PlainFormatter implements Formatter
{
    /**
     * @var OutputFormatter
     */
    private $innerFormatter;

    /**
     * Creates the formatter.
     *
     * @param StyleSet $styleSet The style set to use.
     */
    public function __construct(StyleSet $styleSet = null)
    {
        $this->innerFormatter = new OutputFormatter(false);

        if ($styleSet) {
            foreach ($styleSet->toArray() as $tag => $style) {
                $this->innerFormatter->setStyle($tag, StyleConverter::convert($style));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format($string, Style $style = null)
    {
        return $this->innerFormatter->format($string);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFormat($string)
    {
        return $this->innerFormatter->format($string);
    }
}
