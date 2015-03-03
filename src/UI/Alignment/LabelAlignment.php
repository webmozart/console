<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\UI\Alignment;

use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\UI\Component\LabeledParagraph;

/**
 * Aligns labeled paragraphs.
 *
 * The alignment takes {@link LabeledParagraph} instances and aligns the texts
 * next to the labels so that all texts start at the same offset. Pass the
 * paragraphs that you want to align to {@link add()}. When you call
 * {@link align()}, the text offset is calculated. You can retrieve the
 * calculated offset with {@link getTextOffset()}.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LabelAlignment
{
    /**
     * @var LabeledParagraph[]
     */
    private $paragraphs = array();

    /**
     * @var int[]
     */
    private $indentations = array();

    /**
     * @var int
     */
    private $textOffset = 0;

    /**
     * Adds a labeled paragraph to the alignment.
     *
     * @param LabeledParagraph $paragraph   The labeled paragraph.
     * @param int              $indentation The indentation of the paragraph.
     */
    public function add(LabeledParagraph $paragraph, $indentation = 0)
    {
        if ($paragraph->isAligned()) {
            $this->paragraphs[] = $paragraph;
            $this->indentations[] = $indentation;
        }
    }

    /**
     * Calculates the text offset based on all labels in the alignment.
     *
     * The passed indentation is added to the indentations of all labeled
     * paragraphs.
     *
     * @param Formatter $formatter   The formatter used to remove style tags when
     *                               calculating the label width.
     * @param int       $indentation The indentation.
     */
    public function align(Formatter $formatter, $indentation = 0)
    {
        $this->textOffset = 0;

        foreach ($this->paragraphs as $i => $item) {
            $label = $formatter->removeFormat($item->getLabel());
            $textOffset = $this->indentations[$i] + strlen($label) + $item->getPadding();

            $this->textOffset = max($this->textOffset, $textOffset);
        }

        $this->textOffset += $indentation;
    }

    /**
     * Manually sets the text offset.
     *
     * @param int $textOffset The text offset.
     */
    public function setTextOffset($textOffset)
    {
        $this->textOffset = $textOffset;
    }

    /**
     * Returns the calculated text offset.
     *
     * Before calling {@link align()} or {@link setTextOffset()}, this method
     * returns 0.
     *
     * @return int The text offset.
     */
    public function getTextOffset()
    {
        return $this->textOffset;
    }
}

