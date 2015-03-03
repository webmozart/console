<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\UI\Style;

use Webmozart\Console\Api\Formatter\Style;

/**
 * Defines the style of a border.
 *
 * Use {@link none()}, {@link ascii()} or {@link solid()} to obtain predefined
 * border styles.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BorderStyle
{
    /**
     * @var BorderStyle
     */
    private static $none;

    /**
     * @var BorderStyle
     */
    private static $ascii;

    /**
     * @var BorderStyle
     */
    private static $solid;

    /**
     * @var string
     */
    private $lineHTChar = '-';

    /**
     * @var string
     */
    private $lineHCChar = '-';

    /**
     * @var string
     */
    private $lineHBChar = '-';

    /**
     * @var string
     */
    private $lineVLChar = '|';

    /**
     * @var string
     */
    private $lineVCChar = '|';

    /**
     * @var string
     */
    private $lineVRChar = '|';

    /**
     * @var string
     */
    private $cornerTLChar = '+';

    /**
     * @var string
     */
    private $cornerTRChar = '+';

    /**
     * @var string
     */
    private $cornerBLChar = '+';

    /**
     * @var string
     */
    private $cornerBRChar = '+';

    /**
     * @var string
     */
    private $crossingCChar = '+';

    /**
     * @var string
     */
    private $crossingLChar = '+';

    /**
     * @var string
     */
    private $crossingRChar = '+';

    /**
     * @var string
     */
    private $crossingTChar = '+';

    /**
     * @var string
     */
    private $crossingBChar = '+';

    /**
     * @var Style
     */
    private $style;

    /**
     * A borderless style.
     *
     * @return BorderStyle The style.
     */
    public static function none()
    {
        if (!self::$none) {
            self::$none = new static();
            self::$none->lineVLChar = '';
            self::$none->lineVCChar = ' ';
            self::$none->lineVRChar = '';
            self::$none->lineHTChar = '';
            self::$none->lineHCChar = '';
            self::$none->lineHBChar = '';
            self::$none->cornerTLChar = '';
            self::$none->cornerTRChar = '';
            self::$none->cornerBLChar = '';
            self::$none->cornerBRChar = '';
            self::$none->crossingCChar = '';
            self::$none->crossingLChar = '';
            self::$none->crossingRChar = '';
            self::$none->crossingTChar = '';
            self::$none->crossingBChar = '';
        }

        return clone self::$none;
    }

    /**
     * A style that uses ASCII characters only.
     *
     * @return BorderStyle The style.
     */
    public static function ascii()
    {
        if (!self::$ascii) {
            self::$ascii = new static();
            self::$ascii->lineVLChar = '|';
            self::$ascii->lineVCChar = '|';
            self::$ascii->lineVRChar = '|';
            self::$ascii->lineHTChar = '-';
            self::$ascii->lineHCChar = '-';
            self::$ascii->lineHBChar = '-';
            self::$ascii->cornerTLChar = '+';
            self::$ascii->cornerTRChar = '+';
            self::$ascii->cornerBLChar = '+';
            self::$ascii->cornerBRChar = '+';
            self::$ascii->crossingCChar = '+';
            self::$ascii->crossingLChar = '+';
            self::$ascii->crossingRChar = '+';
            self::$ascii->crossingTChar = '+';
            self::$ascii->crossingBChar = '+';
        }

        return clone self::$ascii;
    }

    /**
     * A style that uses Unicode characters to draw solid lines.
     *
     * @return BorderStyle The style.
     */
    public static function solid()
    {
        if (!self::$solid) {
            self::$solid = new static();
            self::$solid->lineVLChar = '│';
            self::$solid->lineVCChar = '│';
            self::$solid->lineVRChar = '│';
            self::$solid->lineHTChar = '─';
            self::$solid->lineHCChar = '─';
            self::$solid->lineHBChar = '─';
            self::$solid->cornerTLChar = '┌';
            self::$solid->cornerTRChar = '┐';
            self::$solid->cornerBLChar = '└';
            self::$solid->cornerBRChar = '┘';
            self::$solid->crossingCChar = '┼';
            self::$solid->crossingLChar = '├';
            self::$solid->crossingRChar = '┤';
            self::$solid->crossingTChar = '┬';
            self::$solid->crossingBChar = '┴';
        }

        return clone self::$solid;
    }

    /**
     * Returns the character used to draw a horizontal line at the top.
     *
     * @return string The line character.
     */
    public function getLineHTChar()
    {
        return $this->lineHTChar;
    }

    /**
     * Sets the character used to draw a horizontal line at the top.
     *
     * @param string $char The line character.
     *
     * @return static The current instance.
     */
    public function setLineHTChar($char)
    {
        $this->lineHTChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a horizontal line at the center.
     *
     * @return string The line character.
     */
    public function getLineHCChar()
    {
        return $this->lineHCChar;
    }

    /**
     * Sets the character used to draw a horizontal line at the center.
     *
     * @param string $char The line character.
     *
     * @return static The current instance.
     */
    public function setLineHCChar($char)
    {
        $this->lineHCChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a horizontal line at the bottom.
     *
     * @return string The line character.
     */
    public function getLineHBChar()
    {
        return $this->lineHBChar;
    }

    /**
     * Sets the character used to draw a horizontal line at the bottom.
     *
     * @param string $char The line character.
     *
     * @return static The current instance.
     */
    public function setLineHBChar($char)
    {
        $this->lineHBChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a vertical line on the left.
     *
     * @return string The line character.
     */
    public function getLineVLChar()
    {
        return $this->lineVLChar;
    }

    /**
     * Sets the character used to draw a vertical line on the left.
     *
     * @param string $char The line character.
     *
     * @return static The current instance.
     */
    public function setLineVLChar($char)
    {
        $this->lineVLChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a vertical line in the middle.
     *
     * @return string The line character.
     */
    public function getLineVCChar()
    {
        return $this->lineVCChar;
    }

    /**
     * Sets the character used to draw a vertical line in the middle.
     *
     * @param string $char The line character.
     *
     * @return static The current instance.
     */
    public function setLineVCChar($char)
    {
        $this->lineVCChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a vertical line on the right.
     *
     * @return string The line character.
     */
    public function getLineVRChar()
    {
        return $this->lineVRChar;
    }

    /**
     * Sets the character used to draw a vertical line on the right.
     *
     * @param string $char The line character.
     *
     * @return static The current instance.
     */
    public function setLineVRChar($char)
    {
        $this->lineVRChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a corner on the top left.
     *
     * @return string The corner character.
     */
    public function getCornerTLChar()
    {
        return $this->cornerTLChar;
    }

    /**
     * Sets the character used to draw a corner on the top left.
     *
     * @param string $char The corner character.
     *
     * @return static The current instance.
     */
    public function setCornerTLChar($char)
    {
        $this->cornerTLChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a corner on the top right.
     *
     * @return string The corner character.
     */
    public function getCornerTRChar()
    {
        return $this->cornerTRChar;
    }

    /**
     * Sets the character used to draw a corner on the top right.
     *
     * @param string $char The corner character.
     *
     * @return static The current instance.
     */
    public function setCornerTRChar($char)
    {
        $this->cornerTRChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a corner on the bottom left.
     *
     * @return string The corner character.
     */
    public function getCornerBLChar()
    {
        return $this->cornerBLChar;
    }

    /**
     * Sets the character used to draw a corner on the bottom left.
     *
     * @param string $char The corner character.
     *
     * @return static The current instance.
     */
    public function setCornerBLChar($char)
    {
        $this->cornerBLChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a corner on the bottom right.
     *
     * @return string The corner character.
     */
    public function getCornerBRChar()
    {
        return $this->cornerBRChar;
    }

    /**
     * Sets the character used to draw a corner on the bottom right.
     *
     * @param string $char The corner character.
     *
     * @return static The current instance.
     */
    public function setCornerBRChar($char)
    {
        $this->cornerBRChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a crossing at the center.
     *
     * @return string The crossing character.
     */
    public function getCrossingCChar()
    {
        return $this->crossingCChar;
    }

    /**
     * Sets the character used to draw a crossing at the center.
     *
     * @param string $char The crossing character.
     *
     * @return static The current instance.
     */
    public function setCrossingCChar($char)
    {
        $this->crossingCChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a crossing on the left.
     *
     * @return string The crossing character.
     */
    public function getCrossingLChar()
    {
        return $this->crossingLChar;
    }

    /**
     * Sets the character used to draw a crossing on the left.
     *
     * @param string $char The crossing character.
     *
     * @return static The current instance.
     */
    public function setCrossingLChar($char)
    {
        $this->crossingLChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a crossing on the right.
     *
     * @return string The crossing character.
     */
    public function getCrossingRChar()
    {
        return $this->crossingRChar;
    }

    /**
     * Sets the character used to draw a crossing on the right.
     *
     * @param string $char The crossing character.
     *
     * @return static The current instance.
     */
    public function setCrossingRChar($char)
    {
        $this->crossingRChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a crossing at the top.
     *
     * @return string The crossing character.
     */
    public function getCrossingTChar()
    {
        return $this->crossingTChar;
    }

    /**
     * Sets the character used to draw a crossing at the top.
     *
     * @param string $char The crossing character.
     *
     * @return static The current instance.
     */
    public function setCrossingTChar($char)
    {
        $this->crossingTChar = $char;

        return $this;
    }

    /**
     * Returns the character used to draw a crossing at the bottom.
     *
     * @return string The crossing character.
     */
    public function getCrossingBChar()
    {
        return $this->crossingBChar;
    }

    /**
     * Sets the character used to draw a crossing at the bottom.
     *
     * @param string $char The crossing character.
     *
     * @return static The current instance.
     */
    public function setCrossingBChar($char)
    {
        $this->crossingBChar = $char;

        return $this;
    }

    /**
     * Returns the border style.
     *
     * @return Style The border style.
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Sets the border style.
     *
     * @param Style $style The border style.
     *
     * @return static The current instance.
     */
    public function setStyle(Style $style)
    {
        $this->style = $style;

        return $this;
    }
}
