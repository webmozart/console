<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\UI\Layout;

use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\UI\Alignment\LabelAlignment;
use Webmozart\Console\UI\Component;
use Webmozart\Console\UI\Component\LabeledParagraph;

/**
 * Renders renderable objects in indented blocks.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BlockLayout implements Component
{
    /**
     * @var int
     */
    private $currentIndentation = 0;

    /**
     * @var Component[]
     */
    private $elements = array();

    /**
     * @var int[]
     */
    private $indentations = array();

    /**
     * @var LabelAlignment
     */
    private $alignment;

    /**
     * Creates a new layout.
     */
    public function __construct()
    {
        $this->alignment = new LabelAlignment();
    }

    /**
     * Adds a renderable element to the layout.
     *
     * @param Component $element The element to add.
     *
     * @return static The current instance.
     */
    public function add(Component $element)
    {
        $this->elements[] = $element;
        $this->indentations[] = $this->currentIndentation;

        if ($element instanceof LabeledParagraph) {
            $this->alignment->add($element, $this->currentIndentation);
            $element->setAlignment($this->alignment);
        }

        return $this;
    }

    /**
     * Starts a new indented block.
     *
     * @return static The current instance.
     */
    public function beginBlock()
    {
        $this->currentIndentation += 2;

        return $this;
    }

    /**
     * Ends the current indented block.
     *
     * @return static The current instance.
     */
    public function endBlock()
    {
        $this->currentIndentation -= 2;

        return $this;
    }

    /**
     * Renders all elements in the layout.
     *
     * @param IO  $io          The I/O.
     * @param int $indentation The number of spaces to indent.
     */
    public function render(IO $io, $indentation = 0)
    {
        $this->alignment->align($io, $indentation);

        foreach ($this->elements as $i => $element) {
            $element->render($io, $this->indentations[$i] + $indentation);
        }

        $this->elements = array();
    }
}
