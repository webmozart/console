<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Style;

use OutOfBoundsException;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

/**
 * A set of styles used by the output formatter.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StyleSet
{
    /**
     * @var OutputFormatterStyleInterface[]
     */
    private $styles = array();

    /**
     * Creates a new style set.
     *
     * @param OutputFormatterStyleInterface[] $styles The styles indexed by
     *                                                their names.
     */
    public function __construct(array $styles = array())
    {
        $this->setStyles($styles);
    }

    /**
     * Sets the styles of the style set.
     *
     * @param OutputFormatterStyleInterface[] $styles The styles indexed by
     *                                                their names.
     */
    public function setStyles(array $styles)
    {
        $this->styles = array();

        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }
    }

    /**
     * Sets a style.
     *
     * @param string                        $name  The name of the style.
     * @param OutputFormatterStyleInterface $style The style.
     */
    public function setStyle($name, OutputFormatterStyleInterface $style)
    {
        $this->styles[$name] = $style;
    }

    /**
     * Returns whether the style with the given name exists.
     *
     * @param string $name The name of the style.
     *
     * @return bool Returns `true` if a style with the given name exists and
     *              `false` otherwise.
     */
    public function hasStyle($name)
    {
        return isset($this->styles[$name]);
    }

    /**
     * Returns the style with the given name.
     *
     * @param string $name The name of the style.
     *
     * @return OutputFormatterStyleInterface The style.
     *
     * @throws OutOfBoundsException If no style is set for the given name.
     */
    public function getStyle($name)
    {
        if (!isset($this->styles[$name])) {
            throw new OutOfBoundsException(sprintf(
                'The style "%s" is not set.',
                $name
            ));
        }

        return $this->styles[$name];
    }

    /**
     * Returns all styles indexed by their names.
     *
     * @return OutputFormatterStyleInterface[] The styles.
     */
    public function getStyles()
    {
        return $this->styles;
    }
}
