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
use Webmozart\Console\Assert\Assert;

/**
 * Defines the style of a {@link Table}.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TableStyle
{
    /**
     * @var TableStyle
     */
    private static $borderless;

    /**
     * @var TableStyle
     */
    private static $asciiBorder;

    /**
     * @var TableStyle
     */
    private static $solidBorder;

    /**
     * @var string
     */
    private $paddingChar = ' ';

    /**
     * @var string
     */
    private $headerCellFormat = '%s';

    /**
     * @var string
     */
    private $cellFormat = '%s';

    /**
     * @var string
     */
    private $columnAlignments = array();

    /**
     * @var string
     */
    private $defaultColumnAlignment = Alignment::LEFT;

    /**
     * @var BorderStyle
     */
    private $borderStyle;

    /**
     * @var Style
     */
    private $headerCellStyle;

    /**
     * @var Style
     */
    private $cellStyle;

    /**
     * A borderless style.
     *
     * @return TableStyle The style.
     */
    public static function borderless()
    {
        if (!self::$borderless) {
            self::$borderless = new static();
            self::$borderless->borderStyle = BorderStyle::none();
            self::$borderless->borderStyle->setLineHCChar('=');
            self::$borderless->borderStyle->setLineVCChar(' ');
            self::$borderless->borderStyle->setCrossingCChar(' ');
        }

        return clone self::$borderless;
    }

    /**
     * A style that uses ASCII characters for drawing borders.
     *
     * @return TableStyle The style.
     */
    public static function asciiBorder()
    {
        if (!self::$asciiBorder) {
            self::$asciiBorder = new static();
            self::$asciiBorder->headerCellFormat = ' %s ';
            self::$asciiBorder->cellFormat = ' %s ';
            self::$asciiBorder->borderStyle = BorderStyle::ascii();
        }

        return clone self::$asciiBorder;
    }

    /**
     * A style that uses Unicode characters for drawing solid borders.
     *
     * @return TableStyle The style.
     */
    public static function solidBorder()
    {
        if (!self::$solidBorder) {
            self::$solidBorder = new static();
            self::$solidBorder->headerCellFormat = ' %s ';
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
     * Returns the format string for rendering the header cells.
     *
     * @return string The format string. The string contains the substring "%s"
     *                where the cell content is inserted.
     */
    public function getHeaderCellFormat()
    {
        return $this->headerCellFormat;
    }

    /**
     * Sets the format string for rendering the header cells.
     *
     * @param string $format The format string. The string should contain the
     *                       substring "%s" where the cell content is inserted.
     *
     * @return static The current instance.
     */
    public function setHeaderCellFormat($format)
    {
        $this->headerCellFormat = $format;

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
     * Returns the alignments of the table columns.
     *
     * @param int $nbColumns The number of alignments to return.
     *
     * @return int[] An array of {@link Alignment} constants indexed by the
     *               0-based column keys.
     */
    public function getColumnAlignments($nbColumns)
    {
        return array_slice(array_replace(
            array_fill(0, $nbColumns, $this->defaultColumnAlignment),
            $this->columnAlignments
        ), 0, $nbColumns);
    }

    /**
     * Returns the alignment of a given column.
     *
     * @param int $column The 0-based column key.
     *
     * @return int The {@link Alignment} constant.
     */
    public function getColumnAlignment($column)
    {
        return isset($this->columnAlignments[$column])
            ? $this->columnAlignments[$column]
            : $this->defaultColumnAlignment;
    }

    /**
     * Sets the alignments of the table columns.
     *
     * @param int[] $alignments An array of {@link Alignment} constants indexed
     *                          by the 0-based column keys.
     *
     * @return static The current instance.
     */
    public function setColumnAlignments(array $alignments)
    {
        $this->columnAlignments = array();

        foreach ($alignments as $column => $alignment) {
            $this->setColumnAlignment($column, $alignment);
        }

        return $this;
    }

    /**
     * Sets the alignment of a given column.
     *
     * @param int $column    The 0-based column key.
     * @param int $alignment The alignment.
     *
     * @return static The current instance.
     */
    public function setColumnAlignment($column, $alignment)
    {
        Assert::oneOf($alignment, Alignment::all(), 'The column alignment must be one of the Alignment constants. Got: %s');

        $this->columnAlignments[$column] = $alignment;

        return $this;
    }

    /**
     * Returns the default column alignment.
     *
     * @return int One of the {@link Alignment} constants.
     */
    public function getDefaultColumnAlignment()
    {
        return $this->defaultColumnAlignment;
    }

    /**
     * Returns the default column alignment.
     *
     * @param int $alignment One of the {@link Alignment} constants.
     *
     * @return static The current instance.
     */
    public function setDefaultColumnAlignment($alignment)
    {
        Assert::oneOf($alignment, Alignment::all(), 'The default column alignment must be one of the Alignment constants. Got: %s');

        $this->defaultColumnAlignment = $alignment;

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
     * Returns the style of the header cells.
     *
     * @return Style The header cell style.
     */
    public function getHeaderCellStyle()
    {
        return $this->headerCellStyle;
    }

    /**
     * Sets the style of the header cells.
     *
     * @param Style $style The header cell style.
     *
     * @return static The current instance.
     */
    public function setHeaderCellStyle(Style $style = null)
    {
        $this->headerCellStyle = $style;

        return $this;
    }

    /**
     * Returns the style of the table cells.
     *
     * @return Style The cell style.
     */
    public function getCellStyle()
    {
        return $this->cellStyle;
    }

    /**
     * Sets the style of the table cells.
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
