<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Formatter;

use LogicException;
use OutOfBoundsException;

/**
 * A set of styles used by the formatter.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StyleSet
{
    /**
     * @var Style[]
     */
    private $styles = array();

    /**
     * Creates a new style set.
     *
     * @param Style[] $styles The styles to add.
     */
    public function __construct(array $styles = array())
    {
        $this->replace($styles);
    }

    /**
     * Adds a style.
     *
     * @param Style $style The style to add.
     *
     * @throws LogicException If the tag of the style is not set.
     */
    public function add(Style $style)
    {
        if (!$style->getTag()) {
            throw new LogicException('The tag of a style added to the style set must be set.');
        }

        $this->styles[$style->getTag()] = $style;
    }

    /**
     * Adds styles to the style set.
     *
     * Existing styles are preserved.
     *
     * @param Style[] $styles The styles to add.
     */
    public function merge(array $styles)
    {
        foreach ($styles as $style) {
            $this->add($style);
        }
    }

    /**
     * Sets the styles of the style set.
     *
     * Existing styles are removed.
     *
     * @param Style[] $styles The styles to set.
     */
    public function replace(array $styles)
    {
        $this->styles = array();

        $this->merge($styles);
    }

    /**
     * Removes a style.
     *
     * This method does nothing if the tag does not exist.
     *
     * @param string $tag The tag of the style.
     */
    public function remove($tag)
    {
        unset($this->styles[$tag]);
    }

    /**
     * Clears the contents of the style set.
     */
    public function clear()
    {
        $this->styles = array();
    }

    /**
     * Returns whether the style with the given tag exists.
     *
     * @param string $tag The tag of the style.
     *
     * @return bool Returns `true` if a style with the given tag exists and
     *              `false` otherwise.
     */
    public function contains($tag)
    {
        return isset($this->styles[$tag]);
    }

    /**
     * Returns whether the style set is empty.
     *
     * @return bool Returns `true` if the set contains no styles and `false`
     *              otherwise.
     */
    public function isEmpty()
    {
        return 0 === count($this->styles);
    }

    /**
     * Returns the style with the given tag.
     *
     * @param string $tag The tag of the style.
     *
     * @return Style The style.
     *
     * @throws OutOfBoundsException If no style is set for the given tag.
     */
    public function get($tag)
    {
        if (!isset($this->styles[$tag])) {
            throw new OutOfBoundsException(sprintf(
                'The style tag "%s" does not exist.',
                $tag
            ));
        }

        return $this->styles[$tag];
    }

    /**
     * Returns all styles.
     *
     * @return Style[] The styles indexed by their tags.
     */
    public function toArray()
    {
        return $this->styles;
    }
}
