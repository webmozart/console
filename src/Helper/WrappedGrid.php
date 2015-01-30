<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Helper;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class WrappedGrid
{
    const MIN_COLUMNS = 4;

    private $screenWidth;

    private $cells = array();

    private $horizontalSeparator = ' ';

    private $excessWidthBetweenColumns = 0;

    /**
     * @var OutputFormatterInterface
     */
    private $nullFormatter;

    public function __construct($screenWidth = null)
    {
        $this->screenWidth = $screenWidth ?: 80;
    }

    public function getHorizontalSeparator()
    {
        return $this->horizontalSeparator;
    }

    public function setHorizontalSeparator($horizontalSeparator)
    {
        $this->horizontalSeparator = $horizontalSeparator;
    }

    public function addCell($content)
    {
        $this->cells[] = $content;
    }

    public function render(OutputInterface $output)
    {
        $this->nullFormatter = clone $output->getFormatter();
        $this->nullFormatter->setDecorated(false);

        $cellWidths = $this->getCellWidths($this->cells);
        $minCellWidths = $this->getMinCellWidths($this->cells);

        $this->excessWidthBetweenColumns = strlen($this->horizontalSeparator);
        $availableScreenWidth = $this->screenWidth - (self::MIN_COLUMNS - 1)*$this->excessWidthBetweenColumns;
        $maxWidth = floor($availableScreenWidth / self::MIN_COLUMNS);

        $columnWidths = $this->getColumnWidths($cellWidths, $minCellWidths, $maxWidth);

        $wrappedCells = $this->wrapCells($this->cells, $columnWidths);

        $this->renderCells($output, $wrappedCells, $columnWidths);

        $this->nullFormatter = null;
    }

    private function wrapCells(array $cells, array $columnWidths)
    {
        $column = 0;
        $nbColumns = count($columnWidths);
        $rows = $currentRow = array();

        foreach ($cells as $cell) {
            if (0 === $column) {
                $rows[] = array();
                $currentRow = &$rows[count($rows) - 1];
            }

            $columnWidth = $columnWidths[$column];
            $currentRow[] = explode("\n", wordwrap($cell, $columnWidth));
            $column = ($column + 1) % $nbColumns;
        }

        // Fill the last row up
        $currentRow = array_pad($currentRow, $nbColumns, array());

        return $this->rearrangeCells($rows, $nbColumns);
    }

    private function rearrangeCells(array $rows, $nbColumns)
    {
        $cells = array();

        foreach ($rows as $row) {
            $columnsComplete = 0;

            while ($columnsComplete < $nbColumns) {
                foreach ($row as &$cellLines) {
                    $cells[] = $cellLines ? array_shift($cellLines) : '';

                    if (array() === $cellLines) {
                        $cellLines = null;
                        ++$columnsComplete;
                    }
                }
            }
        }

        return $cells;
    }

    private function renderCells(OutputInterface $output, array $cells, array $columnWidths)
    {
        $column = 0;
        $nbColumns = count($columnWidths);

        foreach ($cells as $cell) {
            if (0 !== $column) {
                $output->write($this->horizontalSeparator);
            }

            $columnWidth = $columnWidths[$column];
            $missingSpace = $columnWidth - $this->getTextWidth($cell);

            $output->write($cell.str_repeat(' ', $missingSpace));

            $column = ($column + 1) % $nbColumns;

            if (0 === $column) {
                $output->write("\n");
            }
        }

        // final line break
        if (0 !== $column) {
            $output->write("\n");
        }
    }

    private function getCellWidths(array $cells)
    {
        $widths = array();

        foreach ($cells as $cell) {
            $widths[] = $this->getTextWidth($cell);
        }

        return $widths;
    }

    private function getMinCellWidths(array $cells)
    {
        $minWidths = array();

        foreach ($cells as $cell) {
            $minWidths[] = $this->getMinTextWidth($cell);
        }

        return $minWidths;
    }

    private function getTextWidth($text)
    {
        $width = 0;

        // Remove decoration
        $text = $this->nullFormatter->format($text);

        foreach (explode("\n", $text) as $line) {
            $width = max($width, strlen($line));
        }

        return $width;
    }

    private function getMinTextWidth($text)
    {
        // Remove decoration
        $text = $this->nullFormatter->format($text);

        $spacePos = strpos($text, ' ');
        $nlPos = strpos($text, "\n");

        if (false === $spacePos && false === $nlPos) {
            return strlen($text);
        } elseif (false === $spacePos) {
            return $nlPos;
        } elseif (false === $nlPos) {
            return $spacePos;
        }

        return min($spacePos, $nlPos);
    }

    private function getColumnWidths(array $cellWidths, array $minCellWidths, $maxWidth)
    {
        $nbColumns = $this->calcInitialNumberOfColumns($cellWidths, $minCellWidths, $maxWidth);

        do {
            $widths = $this->calcColumnWidths($cellWidths, $minCellWidths, $maxWidth, $nbColumns);
            $requiredScreenWidth = array_sum($widths) + (count($widths) - 1)*$this->excessWidthBetweenColumns;
            $nbColumns--;
        } while ($requiredScreenWidth > $this->screenWidth);

        return $widths;
    }

    private function calcInitialNumberOfColumns(array $cellWidths, array $minCellWidths, $maxWidth)
    {
        $totalWidth = 0;
        $nbColumns = 0;

        foreach ($cellWidths as $i => $cellWidth) {
            $maxCellWidth = max($maxWidth, $minCellWidths[$i]);
            $totalWidth += min($maxCellWidth, $cellWidth);

            if ($totalWidth > $this->screenWidth) {
                return $nbColumns;
            }

            $totalWidth += $this->excessWidthBetweenColumns;
            $nbColumns++;
        }

        return $nbColumns;
    }

    private function calcColumnWidths(array $cellWidths, array $minCellWidths, $maxWidth, $nbColumns)
    {
        $widths = array_fill(0, $nbColumns, 0);
        $column = 0;

        foreach ($cellWidths as $i => $cellWidth) {
            $maxCellWidth = max($maxWidth, $minCellWidths[$i]);
            $widths[$column] = max($widths[$column], min($maxCellWidth, $cellWidth));
            $column = ($column + 1) % $nbColumns;
        }

        return $widths;
    }
}
