<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering\Element;

use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Rendering\Alignment\LabelAlignment;
use Webmozart\Console\Rendering\Renderable;

/**
 * A paragraph with a label on its left.
 *
 * The paragraph is indented to the right of the label and wrapped into the
 * dimensions of the output. You can align multiple labeled paragraphs by
 * passing a {@link LabelAlignment} to {@link setAlignment()}.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LabeledParagraph implements Renderable
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $padding;

    /**
     * @var string
     */
    private $text;

    /**
     * @var bool
     */
    private $aligned;

    /**
     * @var LabelAlignment
     */
    private $alignment;

    /**
     * Creates a new labeled paragraph.
     *
     * @param string $label   The label.
     * @param string $text    The text.
     * @param int    $padding The padding between the text and label in number
     *                        of spaces.
     * @param bool   $aligned Whether the paragraph should be aligned with the
     *                        other paragraph in its alignment (if one is set).
     */
    public function __construct($label, $text, $padding = 2, $aligned = true)
    {
        $this->label = $label;
        $this->padding = $padding;
        $this->text = $text;
        $this->aligned = $aligned;
    }

    /**
     * Returns the label.
     *
     * @return string The label.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the text.
     *
     * @return string The text.
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns the padding between the label and the text in number of spaces.
     *
     * @return int The number of spaces between the label and the text.
     */
    public function getPadding()
    {
        return $this->padding;
    }

    /**
     * Sets the alignment used to align the paragraph.
     *
     * @param LabelAlignment $alignment The alignment.
     */
    public function setAlignment(LabelAlignment $alignment)
    {
        $this->alignment = $alignment;
    }

    /**
     * Returns whether the paragraph is aligned with other paragraphs in its
     * alignment.
     *
     * @return bool Returns `true` if the paragraph should be aligned with the
     *              other paragraphs in the alignment and `false` otherwise.
     */
    public function isAligned()
    {
        return $this->aligned;
    }

    /**
     * Renders the paragraph.
     *
     * @param IO  $io          The I/O.
     * @param int $indentation The number of spaces to indent.
     */
    public function render(IO $io, $indentation = 0)
    {
        $linePrefix = str_repeat(' ', $indentation);
        $visibleLabel = $io->removeFormat($this->label);
        $styleTagLength = strlen($this->label) - strlen($visibleLabel);

        $textOffset = $this->aligned && $this->alignment ? $this->alignment->getTextOffset() - $indentation : 0;
        $textOffset = max($textOffset, strlen($visibleLabel) + $this->padding);
        $textPrefix = str_repeat(' ', $textOffset);

        // 1 trailing space
        $textWidth = $io->getTerminalDimensions()->getWidth() - 1 - $textOffset - $indentation;
        // TODO replace wordwrap() by implementation that is aware of format codes
        $text = str_replace("\n", "\n".$linePrefix.$textPrefix, wordwrap($this->text, $textWidth));

        // Add the total length of the style tags ("<b>", ...)
        $labelWidth = $textOffset + $styleTagLength;

        $io->write(rtrim(sprintf(
            "%s%-${labelWidth}s%s",
            $linePrefix,
            $this->label,
            rtrim($text)
        ))."\n");
    }
}
