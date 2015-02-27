<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering\Element;

use Webmozart\Console\Api\Formatter\Formatter;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Renderable;
use Webmozart\Console\Util\StringUtil;

/**
 * A grid of cells that are dynamically organized in the console window.
 *
 * You can add data cells with {@link addCell()}. Optionally, you can set the
 * minimum and maximum allowed number of columns with {@link setMinNbColumns()}
 * and {@link setMaxNbColumns()}.
 *
 * If you want to style the grid, pass a {@link GridStyle} to the constructor.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Grid implements Renderable
{
    /**
     * @var GridStyle
     */
    private $style;

    /**
     * @var string[]
     */
    private $cells = array();

    /**
     * @var int
     */
    private $minNbColumns = 4;

    /**
     * @var int
     */
    private $maxNbColumns = PHP_INT_MAX;

    /**
     * Creates a new grid.
     *
     * @param GridStyle $style The rendering style. By default, the grid is
     *                         rendered with the style
     *                         {@link GridStyle::borderless()}.
     */
    public function __construct(GridStyle $style = null)
    {
        $this->style = $style ?: GridStyle::borderless();
    }

    /**
     * Adds a data cell to the grid.
     *
     * @param string $cell The data cell.
     *
     * @return static The current instance.
     */
    public function addCell($cell)
    {
        $this->cells[] = $cell;

        return $this;
    }

    /**
     * Adds data cells to the grid.
     *
     * @param string[] $cells The data cells.
     *
     * @return static The current instance.
     */
    public function addCells(array $cells)
    {
        foreach ($cells as $cell) {
            $this->cells[] = $cell;
        }

        return $this;
    }

    /**
     * Sets the data cells in the grid.
     *
     * @param string[] $cells The data cells to set.
     *
     * @return static The current instance.
     */
    public function setCells(array $cells)
    {
        $this->cells = array();

        $this->addCells($cells);

        return $this;
    }

    /**
     * Returns the minimum number of columns in the grid.
     *
     * The default minimum is 4.
     *
     * @return int The minimum number of columns.
     */
    public function getMinNbColumns()
    {
        return $this->minNbColumns;
    }

    /**
     * Sets the minimum number of columns in the grid.
     *
     * The default minimum is 4.
     *
     * @param int $minNbColumns The minimum number of columns.
     *
     * @return static The current instance.
     */
    public function setMinNbColumns($minNbColumns)
    {
        $this->minNbColumns = $minNbColumns;
        $this->maxNbColumns = max($this->maxNbColumns, $minNbColumns);

        return $this;
    }

    /**
     * Returns the maximum number of columns in the grid.
     *
     * The default maximum is unlimited.
     *
     * @return int The maximum number of columns.
     */
    public function getMaxNbColumns()
    {
        return $this->maxNbColumns;
    }

    /**
     * Sets the maximum number of columns in the grid.
     *
     * The default maximum is unlimited.
     *
     * @param int $maxNbColumns The maximum number of columns.
     *
     * @return static The current instance.
     */
    public function setMaxNbColumns($maxNbColumns)
    {
        $this->minNbColumns = min($this->minNbColumns, $maxNbColumns);
        $this->maxNbColumns = $maxNbColumns;

        return $this;
    }

    /**
     * Renders the grid.
     *
     * @param Canvas $canvas      The canvas to render the grid on.
     * @param int    $indentation The number of spaces to indent.
     */
    public function render(Canvas $canvas, $indentation = 0)
    {
        $io = $canvas->getIO();
        $screenWidth = $canvas->getDimensions()->getWidth();
        $excessColumnWidth = StringUtil::getLength(sprintf($this->style->getCellFormat(), ''), $io);

        $wrapper = $this->getCellWrapper($io, $screenWidth, $excessColumnWidth, $indentation);

        $this->renderRows($io, $wrapper->getWrappedRows(), $wrapper->getColumnLengths(), $excessColumnWidth, $indentation);
    }

    private function getCellWrapper(Formatter $formatter, $screenWidth, $excessColumnWidth, $indentation)
    {
        $borderStyle = $this->style->getBorderStyle();
        $wrapper = new CellWrapper();

        foreach ($this->cells as $cell) {
            $wrapper->addCell($cell);
        }

        $nbColumns = min($this->maxNbColumns, $wrapper->getEstimatedNbColumns($screenWidth));

        do {
            $borderWidth = StringUtil::getLength($borderStyle->getLineVLChar())
                + ($nbColumns - 1) * StringUtil::getLength($borderStyle->getLineVCChar())
                + StringUtil::getLength($borderStyle->getLineVRChar());

            $availableWidth = $screenWidth - $indentation - $borderWidth
                - $nbColumns*$excessColumnWidth;

            $wrapper->fit($availableWidth, $nbColumns, $formatter);

            --$nbColumns;
        } while ($wrapper->hasWordCuts() && $nbColumns >= $this->minNbColumns);

        return $wrapper;
    }

    private function renderRows(IO $io, array $rows, array $columnLengths, $excessColumnLength, $indentation)
    {
        $alignments = array_fill(0, count($columnLengths), $this->style->getCellAlignment());
        $borderStyle = $this->style->getBorderStyle();
        $borderColumnLengths = array_map(function ($length) use ($excessColumnLength) {
            return $length + $excessColumnLength;
        }, $columnLengths);

        BorderUtil::drawTopBorder($io, $borderStyle, $borderColumnLengths, $indentation);

        $last = count($rows) - 1;

        foreach ($rows as $i => $row) {
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

            if ($i < $last) {
                BorderUtil::drawMiddleBorder($io, $borderStyle, $borderColumnLengths, $indentation);
            }
        }

        BorderUtil::drawBottomBorder($io, $borderStyle, $borderColumnLengths, $indentation);
    }
}
