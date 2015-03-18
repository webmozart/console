<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\UI\Component;

use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Util\StringUtil;

/**
 * Wraps cells to fit a given screen width with a given number of columns.
 *
 * You can add data cells with {@link addCell()}. Call {@link fit()} to fit
 * the cells into a given maximum width and number of columns.
 *
 * You can access the rows with the wrapped cells with {@link getWrappedRows()}.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CellWrapper
{
    /**
     * @var string[]
     */
    private $cells = array();

    /**
     * @var int[][]
     */
    private $cellLengths = array();

    /**
     * @var string[][]
     */
    private $wrappedRows = array();

    /**
     * @var int
     */
    private $nbColumns = 0;

    /**
     * @var int[]
     */
    private $columnLengths;

    /**
     * @var bool
     */
    private $wordWraps = false;

    /**
     * @var bool
     */
    private $wordCuts = false;

    /**
     * @var int
     */
    private $maxTotalWidth = 0;

    /**
     * @var int
     */
    private $totalWidth = 0;

    /**
     * Adds a cell to the wrapper.
     *
     * @param string $cell The data cell.
     */
    public function addCell($cell)
    {
        $this->cells[] = rtrim($cell);
    }

    /**
     * Adds cells to the wrapper.
     *
     * @param string[] $cells The data cells.
     */
    public function addCells(array $cells)
    {
        foreach ($cells as $cell) {
            $this->cells[] = rtrim($cell);
        }
    }

    /**
     * Sets the data cells in the wrapper.
     *
     * @param string[] $cells The data cells.
     */
    public function setCells(array $cells)
    {
        $this->cells = array();

        $this->addCells($cells);
    }

    /**
     * Returns the data cells in the wrapper.
     *
     * @return string[] The data cells.
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * Returns the wrapped cells organized by rows and columns.
     *
     * The method {@link fit()} should be called before accessing this method.
     * Otherwise, an empty array is returned.
     *
     * @return string[][] An array of arrays. The first level represents rows,
     *                    the second level the cells in each row.
     */
    public function getWrappedRows()
    {
        return $this->wrappedRows;
    }

    /**
     * Returns the lengths of the wrapped columns.
     *
     * The method {@link fit()} should be called before accessing this method.
     * Otherwise, an empty array is returned.
     *
     * @return int[] The lengths of each column.
     */
    public function getColumnLengths()
    {
        return $this->columnLengths;
    }

    /**
     * Returns the number of wrapped columns.
     *
     * The method {@link fit()} should be called before accessing this method.
     * Otherwise this method returns zero.
     *
     * @return int The number of columns.
     */
    public function getNbColumns()
    {
        return $this->nbColumns;
    }

    /**
     * Returns an estimated number of columns for the given maximum width.
     *
     * @param int $maxTotalWidth The maximum total width of the columns.
     *
     * @return int The estimated number of columns.
     */
    public function getEstimatedNbColumns($maxTotalWidth)
    {
        $i = 0;
        $rowWidth = 0;

        while (isset($this->cells[$i])) {
            $rowWidth += StringUtil::getLength($this->cells[$i]);

            if ($rowWidth > $maxTotalWidth) {
                // Return previous number of columns
                return $i;
            }

            ++$i;
        }

        return $i;
    }

    /**
     * Returns the maximum allowed total width of the columns.
     *
     * The method {@link fit()} should be called before accessing this method.
     * Otherwise this method returns zero.
     *
     * @return int The maximum allowed total width.
     */
    public function getMaxTotalWidth()
    {
        return $this->maxTotalWidth;
    }

    /**
     * Returns the actual total column width.
     *
     * The method {@link fit()} should be called before accessing this method.
     * Otherwise this method returns zero.
     *
     * @return int The actual total column width.
     */
    public function getTotalWidth()
    {
        return $this->totalWidth;
    }

    /**
     * Returns whether any of the cells needed to be wrapped into multiple
     * lines.
     *
     * The method {@link fit()} should be called before accessing this method.
     * Otherwise this method returns `false`.
     *
     * @return boolean Returns `true` if a cell was wrapped into multiple lines
     *                 and `false` otherwise.
     */
    public function hasWordWraps()
    {
        return $this->wordWraps;
    }

    /**
     * Returns whether any of the cells contains words cut in two.
     *
     * The method {@link fit()} should be called before accessing this method.
     * Otherwise this method returns `false`.
     *
     * @return boolean Returns `true` if a cell contains words cut in two and
     *                 `false` otherwise.
     */
    public function hasWordCuts()
    {
        return $this->wordCuts;
    }

    /**
     * Fits the added cells into the given maximum total width with the given
     * number of columns.
     *
     * @param int       $maxTotalWidth The maximum total width of the columns.
     * @param int       $nbColumns     The number of columns to use.
     * @param Formatter $formatter     The formatter used to remove style tags.
     */
    public function fit($maxTotalWidth, $nbColumns, Formatter $formatter)
    {
        $this->resetState($maxTotalWidth, $nbColumns);
        $this->initRows($formatter);

        // If the cells fit within the max width we're good
        if ($this->totalWidth <= $maxTotalWidth) {
            return;
        }

        $this->wrapColumns($formatter);
    }

    private function resetState($maxTotalWidth, $nbColumns)
    {
        $this->wrappedRows = array();
        $this->nbColumns = $nbColumns;
        $this->cellLengths = array();
        $this->columnLengths = array_fill(0, $nbColumns, 0);
        $this->wordWraps = false;
        $this->wordCuts = false;
        $this->maxTotalWidth = $maxTotalWidth;
        $this->totalWidth = 0;
    }

    private function initRows(Formatter $formatter)
    {
        $row = null;
        $col = 0;

        foreach ($this->cells as $i => $cell) {
            if (0 === $col) {
                $this->wrappedRows[] = array();
                $this->cellLengths[] = array();

                $row = &$this->wrappedRows[count($this->wrappedRows) - 1];
                $cellLengths = &$this->cellLengths[count($this->cellLengths) - 1];
            }

            $row[$col] = $cell;
            $cellLengths[$col] = StringUtil::getLength($cell, $formatter);
            $this->columnLengths[$col] = max($this->columnLengths[$col], $cellLengths[$col]);

            $col = ($col + 1) % $this->nbColumns;
        }

        // Fill last row up
        if ($col > 0) {
            while ($col < $this->nbColumns) {
                $row[$col] = '';
                $cellLengths[$col] = 0;
                ++$col;
            }
        }

        $this->totalWidth = array_sum($this->columnLengths);
    }

    private function wrapColumns(Formatter $formatter)
    {
        $availableWidth = $this->maxTotalWidth;
        $longColumnLengths = $this->columnLengths;

        // Filter "short" column, i.e. columns that are not wrapped
        // We distribute the available screen width by the number of columns
        // and decide that all columns that are shorter than their share are
        // "short".
        // This process is repeated until no more "short" columns are found.
        do {
            $threshold = $availableWidth / count($longColumnLengths);
            $repeat = false;

            foreach ($longColumnLengths as $col => $length) {
                if ($length <= $threshold) {
                    $availableWidth -= $length;
                    unset($longColumnLengths[$col]);
                    $repeat = true;
                }
            }
        } while ($repeat);

        // Calculate actual and available width
        $actualWidth = 0;
        $lastAdaptedCol = 0;

        // "Long" columns, i.e. columns that need to be wrapped, are added to
        // the actual width
        foreach ($longColumnLengths as $col => $length) {
            $actualWidth += $length;
            $lastAdaptedCol = $col;
        }

        // Fit columns into available width
        foreach ($longColumnLengths as $col => $length) {
            // Keep ratios of column lengths and distribute them among the
            // available width
            $this->columnLengths[$col] = round(($length / $actualWidth) * $availableWidth);

            if ($col === $lastAdaptedCol) {
                // Fix rounding errors
                $this->columnLengths[$col] += $this->maxTotalWidth - array_sum($this->columnLengths);
            }

            $this->wrapColumn($col, $this->columnLengths[$col], $formatter);

            // Recalculate the column length based on the actual wrapped length
            $this->refreshColumnLength($col);

            // Recalculate the actual width based on the changed length.
            $actualWidth = $actualWidth - $length + $this->columnLengths[$col];
        }

        $this->totalWidth = array_sum($this->columnLengths);
    }

    private function wrapColumn($col, $columnLength, Formatter $formatter)
    {
        foreach ($this->wrappedRows as $i => $row) {
            $cell = $row[$col];
            $cellLength = $this->cellLengths[$i][$col];

            if ($cellLength > $columnLength) {
                $this->wordWraps = true;

                if (!$this->wordCuts) {
                    $minLengthWithoutCut = StringUtil::getMaxWordLength($cell, $formatter);

                    if ($minLengthWithoutCut > $columnLength) {
                        $this->wordCuts = true;
                    }
                }

                // TODO use format aware wrapper
                // true: Words may be cut in two
                $wrappedCell = wordwrap($cell, $columnLength, "\n", true);

                $this->wrappedRows[$i][$col] = $wrappedCell;

                // Refresh cell length
                $this->cellLengths[$i][$col] = StringUtil::getMaxLineLength($wrappedCell, $formatter);
            }
        }
    }

    private function refreshColumnLength($col)
    {
        $this->columnLengths[$col] = 0;

        foreach ($this->wrappedRows as $i => $row) {
            $this->columnLengths[$col] = max($this->columnLengths[$col], $this->cellLengths[$i][$col]);
        }
    }
}
