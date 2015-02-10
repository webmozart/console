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

use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Rendering\Renderable;

/**
 * A paragraph of text.
 *
 * The paragraph is wrapped into the dimensions of the output.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Paragraph implements Renderable
{
    /**
     * @var string
     */
    private $text;

    /**
     * Creates a new paragraph.
     *
     * @param string $text The text of the paragraph.
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Renders the paragraph.
     *
     * @param Output $output      The output.
     * @param int    $indentation The number of spaces to indent.
     */
    public function render(Output $output, $indentation = 0)
    {
        $linePrefix = str_repeat(' ', $indentation);
        $textWidth = $output->getDimensions()->getWidth() - 1 - $indentation;
        $text = str_replace("\n", "\n".$linePrefix, wordwrap($this->text, $textWidth));

        $output->write($linePrefix.rtrim($text)."\n");
    }
}
