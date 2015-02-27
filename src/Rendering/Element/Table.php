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

use LogicException;
use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Renderable;
use Webmozart\Console\Util\StringUtil;

/**
 * A table of rows and columns.
 *
 * You can add rows to the table with {@link addRow()}. You may optionally set
 * a header row with {@link setHeaderRow()}.
 *
 * If you want to style the table, pass a {@link TableStyle} instance to the
 * constructor.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Table implements Renderable
{
    /**
     * @var TableStyle
     */
    private $style;

    /**
     * @var string[]
     */
    private $headerRow = array();

    /**
     * @var string[][]
     */
    private $rows = array();

    /**
     * @var int
     */
    private $nbColumns;

    /**
     * Creates a new table.
     *
     * @param TableStyle $style The rendering style. By default, the table is
     *                          rendered with the style
     *                          {@link TableStyle::asciiBorder()}.
     */
    public function __construct(TableStyle $style = null)
    {
        $this->style = $style ?: TableStyle::asciiBorder();
    }

    /**
     * Sets the header cells of the table.
     *
     * @param string[] $row The header cells.
     *
     * @return static The current instance.
     *
     * @throws LogicException If the row contains more or less columns than
     *                        rows previously added to the table.
     */
    public function setHeaderRow(array $row)
    {
        if (null === $this->nbColumns) {
            $this->nbColumns = count($row);
        } elseif (count($row) !== $this->nbColumns) {
            throw new LogicException(sprintf(
                'Expected the header row to contain %s cells, but got %s.',
                $this->nbColumns,
                count($row)
            ));
        }

        $this->headerRow = array_values($row);

        return $this;
    }

    /**
     * Adds a row to the table.
     *
     * @param string[] $row An array of data cells.
     *
     * @return static The current instance.
     *
     * @throws LogicException If the row contains more or less columns than
     *                        rows previously added to the table.
     */
    public function addRow(array $row)
    {
        if (null === $this->nbColumns) {
            $this->nbColumns = count($row);
        } elseif (count($row) !== $this->nbColumns) {
            throw new LogicException(sprintf(
                'Expected the row to contain %s cells, but got %s.',
                $this->nbColumns,
                count($row)
            ));
        }

        $this->rows[] = array_values($row);

        return $this;
    }

    /**
     * Adds rows to the table.
     *
     * @param string[][] $rows The rows to add.
     *
     * @return static The current instance.
     *
     * @throws LogicException If a row contains more or less columns than
     *                        rows previously added to the table.
     */
    public function addRows(array $rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }

        return $this;
    }

    /**
     * Sets the rows in the table.
     *
     * @param string[][] $rows The rows to set.
     *
     * @return static The current instance.
     *
     * @throws LogicException If a row contains more or less columns than
     *                        rows previously added to the table.
     */
    public function setRows(array $rows)
    {
        $this->rows = array();

        $this->addRows($rows);

        return $this;
    }

    /**
     * Sets a specific row in the table.
     *
     * @param int      $index The row index.
     * @param string[] $row   An array of data cells.
     *
     * @return static The current instance.
     *
     * @throws LogicException If the row contains more or less columns than
     *                        rows previously added to the table.
     */
    public function setRow($index, array $row)
    {
        if (null === $this->nbColumns) {
            $this->nbColumns = count($row);
        } elseif (count($row) !== $this->nbColumns) {
            throw new LogicException(sprintf(
                'Expected the row to contain %s cells, but got %s.',
                $this->nbColumns,
                count($row)
            ));
        }

        $this->rows[$index] = array_values($row);

        return $this;
    }

    /**
     * Renders the table.
     *
     * @param Canvas $canvas      The canvas to render the table on.
     * @param int    $indentation The number of spaces to indent.
     */
    public function render(Canvas $canvas, $indentation = 0)
    {
        $io = $canvas->getIO();
        $screenWidth = $canvas->getDimensions()->getWidth();
        $excessColumnWidth = max(
            StringUtil::getLength(sprintf($this->style->getHeaderCellFormat(), ''), $io),
            StringUtil::getLength(sprintf($this->style->getCellFormat(), ''), $io)
        );

        $wrapper = $this->getCellWrapper($io, $screenWidth, $excessColumnWidth, $indentation);

        $this->renderRows($io, $wrapper->getWrappedRows(), $wrapper->getColumnLengths(), $excessColumnWidth, $indentation);
    }

    private function getCellWrapper(Formatter $formatter, $screenWidth, $excessColumnWidth, $indentation)
    {
        $borderStyle = $this->style->getBorderStyle();
        $borderWidth = StringUtil::getLength($borderStyle->getLineVLChar())
            + ($this->nbColumns - 1) * StringUtil::getLength($borderStyle->getLineVCChar())
            + StringUtil::getLength($borderStyle->getLineVRChar());
        $availableWidth = $screenWidth - $indentation - $borderWidth
            - $this->nbColumns*$excessColumnWidth;

        $wrapper = new CellWrapper();

        foreach ($this->headerRow as $headerCell) {
            $wrapper->addCell($headerCell);
        }

        foreach ($this->rows as $row) {
            foreach ($row as $cell) {
                $wrapper->addCell($cell);
            }
        }

        $wrapper->fit($availableWidth, $this->nbColumns, $formatter);

        return $wrapper;
    }

    private function renderRows(IO $io, array $rows, array $columnLengths, $excessColumnLength, $indentation)
    {
        $alignments = $this->style->getColumnAlignments(count($columnLengths));
        $borderStyle = $this->style->getBorderStyle();
        $borderColumnLengths = array_map(function ($length) use ($excessColumnLength) {
            return $length + $excessColumnLength;
        }, $columnLengths);

        BorderUtil::drawTopBorder($io, $borderStyle, $borderColumnLengths, $indentation);

        if ($this->headerRow) {
            BorderUtil::drawRow(
                $io,
                $borderStyle,
                array_shift($rows),
                $columnLengths,
                $alignments,
                $this->style->getHeaderCellFormat(),
                $this->style->getHeaderCellStyle(),
                $this->style->getPaddingChar(),
                $indentation
            );

            BorderUtil::drawMiddleBorder($io, $borderStyle, $borderColumnLengths, $indentation);
        }

        foreach ($rows as $row) {
            BorderUtil::drawRow(
                $io,
                $borderStyle,
                $row,
                $columnLengths,
                $alignments,
                $this->style->getCellFormat(),
                $this->style->getCellStyle(),
                $this->style->getPaddingChar(),
                $indentation
            );
        }

        BorderUtil::drawBottomBorder($io, $borderStyle, $borderColumnLengths, $indentation);
    }
}
