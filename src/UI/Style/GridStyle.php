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

use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Formatter\Style;

/**
 * Defines the style of a {@link Grid}.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class GridStyle
{
    /**
     * @var GridStyle
     */
    private static $borderless;

    /**
     * @var GridStyle
     */
    private static $asciiBorder;

    /**
     * @var GridStyle
     */
    private static $solidBorder;

    /**
     * @var string
     */
    private $paddingChar = ' ';

    /**
     * @var string
     */
    private $cellFormat = '%s';

    /**
     * @var int
     */
    private $cellAlignment = Alignment::LEFT;

    /**
     * @var BorderStyle
     */
    private $borderStyle;

    /**
     * @var Style
     */
    private $cellStyle;

    /**
     * A borderless style.
     *
     * @return GridStyle The style.
     */
    public static function borderless()
    {
        if (!self::$borderless) {
            self::$borderless = new static();
            self::$borderless->borderStyle = BorderStyle::none();
        }

        return clone self::$borderless;
    }

    /**
     * A style that uses ASCII characters for drawing borders.
     *
     * @return GridStyle The style.
     */
    public static function asciiBorder()
    {
        if (!self::$asciiBorder) {
            self::$asciiBorder = new static();
            self::$asciiBorder->cellFormat = ' %s ';
            self::$asciiBorder->borderStyle = BorderStyle::ascii();
        }

        return clone self::$asciiBorder;
    }

    /**
     * A style that uses Unicode characters for drawing solid borders.
     *
     * @return GridStyle The style.
     */
    public static function solidBorder()
    {
        if (!self::$solidBorder) {
            self::$solidBorder = new static();
            self::$solidBorder->cellFormat = ' %s ';
            self::$solidBorder->borderStyle = BorderStyle::solid();
        }

        return clone self::$solidBorder;
    }

    /**
     * Returns the character used to pad cells to the desired width.
     *
     * @return string The padding character.
     */
    public function getPaddingChar()
    {
        return $this->paddingChar;
    }

    /**
     * Sets the character used to pad cells to the desired width.
     *
     * @param string $char The padding character.
     *
     * @return static The current instance.
     */
    public function setPaddingChar($char)
    {
        $this->paddingChar = $char;

        return $this;
    }

    /**
     * Returns the format string for rendering cells.
     *
     * @return string The format string. The string contains the substring "%s"
     *                where the cell content is inserted.
     */
    public function getCellFormat()
    {
        return $this->cellFormat;
    }

    /**
     * Sets the format string for rendering cells.
     *
     * @param string $format The format string. The string should contain the
     *                       substring "%s" where the cell content is inserted.
     *
     * @return static The current instance.
     */
    public function setCellFormat($format)
    {
        $this->cellFormat = $format;

        return $this;
    }

    /**
     * Returns the cell alignment.
     *
     * @return int One of the {@link Alignment} constants.
     */
    public function getCellAlignment()
    {
        return $this->cellAlignment;
    }

    /**
     * Sets the cell alignment.
     *
     * @param int $alignment One of the {@link Alignment} constants.
     *
     * @return static The current instance.
     */
    public function setCellAlignment($alignment)
    {
        Assert::oneOf($alignment, Alignment::all(), 'The cell alignment must be one of the Alignment constants. Got: %s');

        $this->cellAlignment = $alignment;

        return $this;
    }

    /**
     * Returns the border style.
     *
     * @return BorderStyle The border style.
     */
    public function getBorderStyle()
    {
        return $this->borderStyle;
    }

    /**
     * Sets the border style.
     *
     * @param BorderStyle $borderStyle The border style.
     *
     * @return static The current instance.
     */
    public function setBorderStyle(BorderStyle $borderStyle)
    {
        $this->borderStyle = $borderStyle;

        return $this;
    }

    /**
     * Returns the style of the grid cells.
     *
     * @return Style The cell style.
     */
    public function getCellStyle()
    {
        return $this->cellStyle;
    }

    /**
     * Sets the style of the grid cells.
     *
     * @param Style $style The cell style.
     *
     * @return static The current instance.
     */
    public function setCellStyle(Style $style)
    {
        $this->cellStyle = $style;

        return $this;
    }
}
