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

use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\UI\Style\Alignment;
use Webmozart\Console\UI\Style\BorderStyle;
use Webmozart\Console\Util\StringUtil;

/**
 * Contains utility methods to draw borders and bordered cells.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BorderUtil
{
    /**
     * Draws a top border.
     *
     * Crossings are drawn between each pair of columns if more than one column
     * length is passed.
     *
     * @param IO          $io            The I/O.
     * @param BorderStyle $style         The border style.
     * @param int[]       $columnLengths An array of column lengths.
     * @param int         $indentation   The number of spaces to indent.
     */
    public static function drawTopBorder(IO $io, BorderStyle $style, array $columnLengths, $indentation = 0)
    {
        self::drawBorder(
            $io,
            $columnLengths,
            $indentation,
            $style->getLineHTChar(),
            $style->getCornerTLChar(),
            $style->getCrossingTChar(),
            $style->getCornerTRChar(),
            $style->getStyle()
        );
    }

    /**
     * Draws a middle border.
     *
     * Crossings are drawn between each pair of columns if more than one column
     * length is passed.
     *
     * @param IO          $io            The I/O.
     * @param BorderStyle $style         The border style.
     * @param int[]       $columnLengths An array of column lengths.
     * @param int         $indentation   The number of spaces to indent.
     */
    public static function drawMiddleBorder(IO $io, BorderStyle $style, array $columnLengths, $indentation = 0)
    {
        self::drawBorder(
            $io,
            $columnLengths,
            $indentation,
            $style->getLineHCChar(),
            $style->getCrossingLChar(),
            $style->getCrossingCChar(),
            $style->getCrossingRChar(),
            $style->getStyle()
        );
    }

    /**
     * Draws a bottom border.
     *
     * Crossings are drawn between each pair of columns if more than one column
     * length is passed.
     *
     * @param IO          $io            The I/O.
     * @param BorderStyle $style         The border style.
     * @param int[]       $columnLengths An array of column lengths.
     * @param int         $indentation   The number of spaces to indent.
     */
    public static function drawBottomBorder(IO $io, BorderStyle $style, array $columnLengths, $indentation = 0)
    {
        self::drawBorder(
            $io,
            $columnLengths,
            $indentation,
            $style->getLineHBChar(),
            $style->getCornerBLChar(),
            $style->getCrossingBChar(),
            $style->getCornerBRChar(),
            $style->getStyle()
        );
    }

    /**
     * Draws a bordered row of cells.
     *
     * @param IO          $io            The I/O.
     * @param BorderStyle $style         The border style.
     * @param string[]    $row           The row cells.
     * @param int[]       $columnLengths The lengths of the cells.
     * @param int[]       $alignments    The alignments of the cells.
     * @param string      $cellFormat    The cell format.
     * @param Style       $cellStyle     The cell style.
     * @param string      $paddingChar   The character used to pad cells.
     * @param int         $indentation   The number of spaces to indent.
     */
    public static function drawRow(IO $io, BorderStyle $style, array $row, array $columnLengths, array $alignments, $cellFormat, Style $cellStyle = null, $paddingChar, $indentation = 0)
    {
        $totalLines = 0;

        // Split all cells into lines
        foreach ($row as $col => $cell) {
            $row[$col] = explode("\n", $cell);
            $totalLines = max($totalLines, count($row[$col]));
        }

        $nbColumns = count($row);
        $borderVLChar = $io->format($style->getLineVLChar(), $style->getStyle());
        $borderVCChar = $io->format($style->getLineVCChar(), $style->getStyle());
        $borderVRChar = $io->format($style->getLineVRChar(), $style->getStyle());

        for ($i = 0; $i < $totalLines; ++$i) {
            $line = str_repeat(' ', $indentation);
            $line .= $borderVLChar;

            foreach ($row as $col => &$remainingLines) {
                $cellLine = $remainingLines ? array_shift($remainingLines) : '';
                $totalPadLength = $columnLengths[$col] - StringUtil::getLength($cellLine, $io);
                $paddingLeft = '';
                $paddingRight = '';

                if ($totalPadLength > 0) {
                    $alignment = isset($alignments[$col]) ? $alignments[$col] : Alignment::LEFT;

                    switch ($alignment) {
                        case Alignment::LEFT:
                            $paddingRight = str_repeat($paddingChar, $totalPadLength);
                            break;
                        case Alignment::RIGHT:
                            $paddingLeft = str_repeat($paddingChar, $totalPadLength);
                            break;
                        case Alignment::CENTER:
                            $leftPadLength = floor($totalPadLength / 2);
                            $paddingLeft = str_repeat($paddingChar, $leftPadLength);
                            $paddingRight = str_repeat($paddingChar, $totalPadLength - $leftPadLength);
                            break;
                    }
                }

                $line .= $io->format(sprintf($cellFormat, $paddingLeft.$cellLine.$paddingRight), $cellStyle);
                $line .= $col < $nbColumns - 1 ? $borderVCChar : $borderVRChar;
            }

            // Remove trailing space
            $io->write(rtrim($line)."\n");
        }
    }

    private static function drawBorder(IO $io, array $columnLengths, $indentation, $lineChar, $crossingLChar, $crossingCChar, $crossingRChar, Style $style = null)
    {
        $line = str_repeat(' ', $indentation);
        $line .= $crossingLChar;

        for ($i = 0, $l = count($columnLengths); $i < $l; ++$i) {
            $line .= str_repeat($lineChar, $columnLengths[$i]);
            $line .= $i < $l - 1 ? $crossingCChar : $crossingRChar;
        }

        // Remove trailing space
        $line = rtrim($line);

        // Render only non-empty separators
        if ($line) {
            $io->write($io->format($line, $style)."\n");
        }
    }

    private function __construct()
    {
    }
}
