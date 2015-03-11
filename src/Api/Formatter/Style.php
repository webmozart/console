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

use Webmozart\Assert\Assert;

/**
 * A formatter style.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Style
{
    /**
     * Color: black
     */
    const BLACK = 'black';

    /**
     * Color: red
     */
    const RED = 'red';

    /**
     * Color: green
     */
    const GREEN = 'green';

    /**
     * Color: yellow
     */
    const YELLOW = 'yellow';

    /**
     * Color: blue
     */
    const BLUE = 'blue';

    /**
     * Color: magenta
     */
    const MAGENTA = 'magenta';

    /**
     * Color: cyan
     */
    const CYAN = 'cyan';

    /**
     * Color: white
     */
    const WHITE = 'white';

    /**
     * @var string[]
     */
    private static $colors = array(
        self::BLACK,
        self::RED,
        self::GREEN,
        self::YELLOW,
        self::BLUE,
        self::MAGENTA,
        self::CYAN,
        self::WHITE,
    );

    /**
     * @var string
     */
    private $tag;

    /**
     * @var string
     */
    private $fgColor;

    /**
     * @var string
     */
    private $bgColor;

    /**
     * @var bool
     */
    private $bold = false;

    /**
     * @var bool
     */
    private $underlined = false;

    /**
     * @var bool
     */
    private $blinking = false;

    /**
     * @var bool
     */
    private $reversed = false;

    /**
     * @var bool
     */
    private $concealed = false;

    /**
     * Creates a style with the given tag name.
     *
     * @param string $tag The tag name.
     *
     * @return static The created style.
     *
     * @see noTag()
     */
    public static function tag($tag)
    {
        return new static($tag);
    }

    /**
     * Creates a style without a tag name.
     *
     * @return static The created style.
     *
     * @see tag()
     */
    public static function noTag()
    {
        return new static();
    }

    /**
     * Creates a style.
     *
     * @param string $tag The tag name.
     */
    public function __construct($tag = null)
    {
        $this->tag = $tag;
    }

    /**
     * Sets the foreground color.
     *
     * @param string $color One of the color constants.
     *
     * @return static The current instance.
     */
    public function fg($color)
    {
        Assert::nullOrOneOf($color, self::$colors, 'The color must be null or one of the Style::* color constants. Got: "%s"');

        $this->fgColor = $color;

        return $this;
    }

    /**
     * Resets the foreground color to the system's default.
     *
     * @return static The current instance.
     */
    public function fgDefault()
    {
        $this->fgColor = null;

        return $this;
    }

    /**
     * Sets the foreground color to black.
     *
     * @return static The current instance.
     */
    public function fgBlack()
    {
        $this->fgColor = self::BLACK;

        return $this;
    }

    /**
     * Sets the foreground color to red.
     *
     * @return static The current instance.
     */
    public function fgRed()
    {
        $this->fgColor = self::RED;

        return $this;
    }

    /**
     * Sets the foreground color to green.
     *
     * @return static The current instance.
     */
    public function fgGreen()
    {
        $this->fgColor = self::GREEN;

        return $this;
    }

    /**
     * Sets the foreground color to yellow.
     *
     * @return static The current instance.
     */
    public function fgYellow()
    {
        $this->fgColor = self::YELLOW;

        return $this;
    }

    /**
     * Sets the foreground color to blue.
     *
     * @return static The current instance.
     */
    public function fgBlue()
    {
        $this->fgColor = self::BLUE;

        return $this;
    }

    /**
     * Sets the foreground color to magenta.
     *
     * @return static The current instance.
     */
    public function fgMagenta()
    {
        $this->fgColor = self::MAGENTA;

        return $this;
    }

    /**
     * Sets the foreground color to cyan.
     *
     * @return static The current instance.
     */
    public function fgCyan()
    {
        $this->fgColor = self::CYAN;

        return $this;
    }

    /**
     * Sets the foreground color to white.
     *
     * @return static The current instance.
     */
    public function fgWhite()
    {
        $this->fgColor = self::WHITE;

        return $this;
    }

    /**
     * Sets the background color.
     *
     * @param string $color One of the color constants.
     *
     * @return static The current instance.
     */
    public function bg($color)
    {
        Assert::nullOrOneOf($color, self::$colors, 'The color must be null or one of the Style::* color constants. Got: "%s"');

        $this->bgColor = $color;

        return $this;
    }

    /**
     * Resets the background color to the system's default.
     *
     * @return static The current instance.
     */
    public function bgDefault()
    {
        $this->bgColor = null;

        return $this;
    }

    /**
     * Sets the background color to black.
     *
     * @return static The current instance.
     */
    public function bgBlack()
    {
        $this->bgColor = self::BLACK;

        return $this;
    }

    /**
     * Sets the background color to red.
     *
     * @return static The current instance.
     */
    public function bgRed()
    {
        $this->bgColor = self::RED;

        return $this;
    }

    /**
     * Sets the background color to green.
     *
     * @return static The current instance.
     */
    public function bgGreen()
    {
        $this->bgColor = self::GREEN;

        return $this;
    }

    /**
     * Sets the background color to yellow.
     *
     * @return static The current instance.
     */
    public function bgYellow()
    {
        $this->bgColor = self::YELLOW;

        return $this;
    }

    /**
     * Sets the background color to blue.
     *
     * @return static The current instance.
     */
    public function bgBlue()
    {
        $this->bgColor = self::BLUE;

        return $this;
    }

    /**
     * Sets the background color to magenta.
     *
     * @return static The current instance.
     */
    public function bgMagenta()
    {
        $this->bgColor = self::MAGENTA;

        return $this;
    }

    /**
     * Sets the background color to cyan.
     *
     * @return static The current instance.
     */
    public function bgCyan()
    {
        $this->bgColor = self::CYAN;

        return $this;
    }

    /**
     * Sets the background color to white.
     *
     * @return static The current instance.
     */
    public function bgWhite()
    {
        $this->bgColor = self::WHITE;

        return $this;
    }

    /**
     * Sets the font weight to bold.
     *
     * @return static The current instance.
     */
    public function bold()
    {
        $this->bold = true;

        return $this;
    }

    /**
     * Sets the font weight to normal.
     *
     * @return static The current instance.
     */
    public function notBold()
    {
        $this->bold = false;

        return $this;
    }

    /**
     * Enables underlining.
     *
     * @return static The current instance.
     */
    public function underlined()
    {
        $this->underlined = true;

        return $this;
    }

    /**
     * Disables underlining.
     *
     * @return static The current instance.
     */
    public function notUnderlined()
    {
        $this->underlined = false;

        return $this;
    }

    /**
     * Enables blinking.
     *
     * @return static The current instance.
     */
    public function blinking()
    {
        $this->blinking = true;

        return $this;
    }

    /**
     * Disables blinking.
     *
     * @return static The current instance.
     */
    public function notBlinking()
    {
        $this->blinking = false;

        return $this;
    }

    /**
     * Enables reversed text.
     *
     * @return static The current instance.
     */
    public function reversed()
    {
        $this->reversed = true;

        return $this;
    }

    /**
     * Disables reversed text.
     *
     * @return static The current instance.
     */
    public function notReversed()
    {
        $this->reversed = false;

        return $this;
    }

    /**
     * Enables concealed text.
     *
     * @return static The current instance.
     */
    public function concealed()
    {
        $this->concealed = true;

        return $this;
    }

    /**
     * Disables concealed text.
     *
     * @return static The current instance.
     */
    public function notConcealed()
    {
        $this->concealed = false;

        return $this;
    }

    /**
     * Returns the style's tag name.
     *
     * @return string The tag name or `null` if the style has no tag.
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Returns the foreground color.
     *
     * @return string One of the color constants or `null` if the system's
     *                default should be used.
     */
    public function getForegroundColor()
    {
        return $this->fgColor;
    }

    /**
     * Returns the background color.
     *
     * @return string One of the color constants or `null` if the system's
     *                default should be used.
     */
    public function getBackgroundColor()
    {
        return $this->bgColor;
    }

    /**
     * Returns whether the text is bold.
     *
     * @return bool Returns `true` if text is formatted bold and `false`
     *              otherwise.
     */
    public function isBold()
    {
        return $this->bold;
    }

    /**
     * Returns whether the text is underlined.
     *
     * @return bool Returns `true` if text is formatted underlined and `false`
     *              otherwise.
     */
    public function isUnderlined()
    {
        return $this->underlined;
    }

    /**
     * Returns whether the text is blinking.
     *
     * @return bool Returns `true` if text is formatted blinking and `false`
     *              otherwise.
     */
    public function isBlinking()
    {
        return $this->blinking;
    }

    /**
     * Returns whether the text is reversed.
     *
     * @return bool Returns `true` if text is formatted reversed and `false`
     *              otherwise.
     */
    public function isReversed()
    {
        return $this->reversed;
    }

    /**
     * Returns whether the text is concealed.
     *
     * @return bool Returns `true` if text is formatted concealed and `false`
     *              otherwise.
     */
    public function isConcealed()
    {
        return $this->concealed;
    }
}
